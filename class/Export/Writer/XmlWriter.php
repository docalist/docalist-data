<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Writer;

use Docalist\Data\Export\Writer;
use XMLWriter as PhpXmlWriter;

/**
 * Générateur XML pour l'export de données Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class XmlWriter implements Writer
{
    /**
     * Taille de l'indentation (nombre d'espaces) ou zéro pour générer du code XML compact.
     *
     * @var int
     */
    protected $indent = 0;

    /**
     * Modifie la taille de l'indentation du code XML généré.
     *
     * @param int $indent Taille de l'indentation (nombre d'espaces) ou zéro pour générer du code XML compact.
     *
     * @eturn self
     */
    public function setIndent(int $indent): self
    {
        $this->indent = abs((int) $indent);

        return $this;
    }

    /**
     * Indique la taille de l'indentation du code XML généré (ou zéro si le XML n'est pas formatté).
     *
     * @return int
     */
    public function getIndent(): int
    {
        return $this->indent;
    }

    public function getContentType(): string
    {
        return 'application/xml; charset=utf-8';
    }

    public function isBinaryContent(): bool
    {
        return false;
    }

    public function suggestFilename(): string
    {
        return 'export.xml';
    }

    public function export(Iterable $records)
    {
        $xml = new PhpXmlWriter();
        $xml->openURI('php://output');

        $indent = $this->getIndent();
        if ($indent > 0) {
            $xml->setIndentString(str_repeat(' ', $indent));
            $xml->setIndent(true);
        }
        $xml->startDocument('1.0', 'utf-8', 'yes');
        $xml->startElement('records');
        foreach ($records as $record) {
            $xml->startElement('record');
            $this->outputArray($xml, $record);
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();

        $xml->flush();
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
                $xml->writeElement($key);
                continue;
            }

            is_int($key) && $key = 'item';
            $xml->startElement($key);
            is_scalar($value) ? $xml->text((string)$value) : $this->outputArray($xml, $value);
            $xml->endElement();
        }
    }
}
