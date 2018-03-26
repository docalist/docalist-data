<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Exporter;

use Docalist\Data\Export\Exporter;
use Docalist\Data\Export\Converter;
use Docalist\Data\Export\Writer;
use Docalist\Data\Export\Writer\AbstractWriter;
use Docalist\Data\Record;
use InvalidArgumentException;
use Generator;

/**
 * Classe de base pour les exporteurs standard.
 *
 * Un exporteur standard définit un pipeline de données dans lequel passent les enregistrements à exporter pour
 * générer un fichier d'export :
 *
 * Record* -> [RecordTransformer*] -> [Converter] -> array -> [ArrayTransformer*] -> [Writer] -> File.
 *
 * Il se compose :
 * - d'une liste d'objets RecordTransformer qui filtrent et transforment les enregistrements à exporter.
 * - d'un objet Converter qui convertit un enregistrement Docalist vers un autre format (un tableau).
 * - d'une liste d'objets ArrayTransformer qui modifient filtrent et transforment les données converties.
 * - d'un objet ExportWriter qui écrit les données obtenues dans un flux de sortie.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class StandardExporter extends AbstractWriter implements Exporter
{
    /**
     * La liste des filtres qui composent le pipeline de données.
     *
     * @var callable[]
     */
    protected $filters;

    /**
     * Le Writer à utiliser pour générer le fichier d'export.
     *
     * @var Writer
     */
    protected $writer;

    /**
     * Initialise l'exporteur.
     *
     * @param callable[]    $filters    La liste des filtres qui composent le pipeline de données.
     * @param Writer        $writer     Le Writer à utiliser pour générer le fichier d'export.
     *
     * @throws InvalidArgumentException Si l'un des filtres n'est pas un callable.
     */
    public function __construct(array $filters, Writer $writer)
    {
        foreach ($filters as $key => $filter) {
            if (! is_callable($filter)) {
                throw new InvalidArgumentException("Filter $key is not callable");
            }
        }
        $this->filters = $filters;
        $this->writer = $writer;
    }

    /**
     * Retourne la liste des filtres.
     *
     * @return callable[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Retourne un filtre.
     *
     * @param int|string $key Clé du filtre (clé associée au filtre dans le tableau de filtres passé au constructeur).
     *
     * @throws InvalidArgumentException Si la clé indiquée n'existe pas.
     *
     * @return callable
     */
    public function getFilter($key)
    {
        if (! isset($this->filters[$key])) {
            throw new InvalidArgumentException('Filter not found');
        }

        return $this->filters[$key];
    }

    /**
     * Retourne le Writer utilisé.
     *
     * @return Writer
     */
    public function getWriter()
    {
        return $this->writer;
    }

    public function getContentType()
    {
        return $this->getWriter()->getContentType();
    }

    public function isBinaryContent()
    {
        return $this->getWriter()->isBinaryContent();
    }

    public function suggestFilename()
    {
        return static::getID() . '-' . $this->getWriter()->suggestFilename();
    }

    public function export($stream, Iterable $records)
    {
        return $this->getWriter()->export($stream, $this->convert($records));
    }

    /**
     * Retourne un générateur qui convertit les enregistrements passés en paramètres.
     *
     * @param Iterable $records Enregistrement à convertir.
     *
     * @return Generator
     */
    protected function convert(Iterable $records)
    {
        foreach ($records as $key => $record) {
            foreach ($this->filters as $filter) {
                if (is_null($record = $filter($record))) {
                    continue 2;
                }
            }

            yield $key => $record;
        }
    }
}
