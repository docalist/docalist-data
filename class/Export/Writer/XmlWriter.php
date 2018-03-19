<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Writer;

use Docalist\Data\Export\Writer;
use Docalist\Data\Export\Converter\WriteError;
use XMLWriter as PhpXmlWriter;

/**
 * Générateur XML pour l'export de données Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class XmlWriter extends AbstractWriter
{
    protected static $defaultSettings = [
        // Surcharge les paramètres hérités
        'mime-type' => 'application/xml',
        'extension' => '.xml',

        // Taille de l'indentation ou zéro ou false pour générer un code compact
        'indent' => 0,
        'binary' => true,
    ];

    /**
     * Nombre d'enregistrements conservés en mémoire avant que flushBuffer() ne soit appellé.
     *
     * @var integer
     */
    const BUFFER_COUNT = 10;

    public function getContentType()
    {
        return 'application/xml; charset=utf-8';
    }

    public function isBinaryContent()
    {
        return false;
    }

    public function suggestFilename()
    {
        return 'export.xml';
    }

    public function export($stream, Iterable $records)
    {
        $xml = new PhpXmlWriter();
        $xml->openMemory();

        $indent=false;
        if ($indent/* = $this->get('indent')*/) {
            $xml->setIndentString(str_repeat(' ', $indent));
            $xml->setIndent(true);
        }
        $xml->startDocument('1.0', 'utf-8', 'yes');
        $xml->startElement('records');
        // $xml->writeAttribute('count', $records->count());
        // $xml->writeAttribute('datetime', date('Y-m-d H:i:s'));
        // $xml->writeAttribute('query', $records->getSearchRequest()->getEquation());
        $nb = 0;
        foreach ($records as $record) {
            $xml->startElement('record');
            $this->outputArray($xml, $record);
            $xml->endElement();
            ++$nb;
            if (0 === $nb % self::BUFFER_COUNT) {
                $this->flushBuffer($stream, $xml);
            }
        }
        $xml->endElement();
        $xml->endDocument();

        $this->flushBuffer($stream, $xml);
    }

    /**
     * Ecrit le buffer XML dans le flux de sortie passé en paramètre et vide le buffer.
     *
     * @param resource      $stream     Flux de sortie.
     * @param PhpXmlWriter  $xml        Objet PhpXmlWriter à flusher.
     *
     * @throw WriteError Si une erreur survient lors de l'écriture des données.
     */
    protected function flushBuffer($stream, PhpXmlWriter $xml)
    {
        // Récupère le buffer XML et vide le buffer de l'objet XMLWriter
        $buffer = $xml->flush(true);
        if (0 === strlen($buffer)) {
            return;
        }

        $size = fwrite($stream, $buffer);
        if ($size === false || $size !== strlen($buffer)) {
            throw new WriteError('An error occured during export');
        }
    }

    /**
     * Exporte le tableau passé en paramètre en xml.
     *
     * Le mappage est très simple :
     * - chaque élément du tableau devient un élément xml.
     * - si la clé est numérique, un noeud "item" est généré.
     * - sinon le nom du noeud correspond au nom de la clé.
     * - si l'élément du tableau est un scalaire, il est écrit tel quel
     * - si c'est un tableau, on récursive.
     *
     * @param PhpXmlWriter  $xml
     * @param array         $data
     */
    protected function outputArray(PhpXmlWriter $xml, array $data)
    {
        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            is_int($key) && $key = 'item';
            $xml->startElement($key);
            is_scalar($value) ? $xml->text($value) : $this->outputArray($xml, $value);
            $xml->endElement();
        }
    }
}
