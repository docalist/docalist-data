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
use Docalist\Data\Export\Writer\AbstractWriter;
use Docalist\Data\Export\RecordProcessor;
use Docalist\Data\Export\Converter;
use Docalist\Data\Export\DataProcessor;
use Docalist\Data\Export\Writer;
use Docalist\Data\Record;
use Generator;

/**
 * Classe de base pour les exporteurs standard.
 *
 * Un exporteur standard définit un pipeline dans lequel passent les enregistrements à exporter pour générer un
 * fichier d'export :
 *
 * Record* -> [RecordProcessor*] -> [Converter] -> array -> [DataConverter*] -> [Writer] -> File.
 *
 * Il se compose :
 * - d'une liste d'objets RecordProcessor qui filtrent et transforment les enregistrements à exporter.
 * - d'un objet Converter qui convertit un enregistrement Docalist vers un autre format.
 * - d'une liste d'objets DataProcessor qui modifient filtrent et transforment les données converties.
 * - d'un objet ExportWriter qui écrit les données obtenues dans un flux de sortie.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class StandardExporter extends AbstractWriter implements Exporter
{
    /**
     * Une liste d'objets RecordProcessor à appliquer avant la conversion.
     *
     * @var RecordProcessor[]
     */
    protected $recordProcessors;

    /**
     * L'objet Converter utilisé pour convertir les enregistrements Docalist.
     *
     * @var RecordConverter
     */
    protected $converter;

    /**
     * Une liste d'objets DataProcessor à appliquer après la conversion.
     *
     * @var DataProcessor[]
     */
    protected $dataProcessors;

    /**
     * Le Writer à utiliser pour générer le fichier d'export.
     *
     * @var Writer
     */
    protected $writer;

    /**
     * Initialise l'exporteur.
     */
    public function __construct()
    {
        // Initialise les RecordProcessors
        $this->recordProcessors = $this->initRecordProcessors();

        // Initialise le convertisseur
        $this->converter = $this->initConverter();

        // Initialise les DataProcessors
        $this->dataProcessors = $this->initDataProcessors();

        // Initialise le Writer
        $this->writer = $this->initWriter();
    }

    /**
     * Initialise et retourne les RecordProcessor à appliquer avant la conversion.
     *
     * @return RecordProcessor[]
     *
     * @return self
     */
    protected function initRecordProcessors()
    {
        return [];
    }

    /**
     * Retourne la liste des objets RecordProcessor appliqués avant la conversion.
     *
     * @return RecordProcessor[] Un tableau d'objets RecordProcessor.
     */
    public function getRecordProcessors()
    {
        return $this->recordProcessors;
    }

    /**
     * Initialise et retourne le Converter à utiliser.
     *
     * @return Converter
     */
    abstract protected function initConverter();

    /**
     * Retourne le convertisseur utilisé.
     *
     * @return Converter
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * Retourne la liste des objets DataProcessor appliqués après la conversion.
     *
     * @return DataProcessor[] Un tableau d'objets DataProcessor.
     */
    protected function initDataProcessors()
    {
        return [];
    }

    /**
     * Retourne la liste des objets DataProcessor appliqués après la conversion.
     *
     * @return DataProcessor[] Un tableau d'objets DataProcessor.
     */
    public function getDataProcessors()
    {
        return $this->dataProcessors;
    }

    /**
     * Initialise et retourne le Writer à utiliser.
     *
     * @return Converter
     */
    abstract protected function initWriter();

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
        return $this->getWriter()->suggestFilename();
    }

    public function export($stream, Iterable $records)
    {
        // Traitement des enregistrements avant conversion
        $processors = $this->getRecordProcessors();
        !empty($processors) && $records = $this->process($records, $processors);

        // Conversion
        $records = $this->convert($records);

        // Traitement des données après conversion
        $processors = $this->getDataProcessors();
        !empty($processors) && $records = $this->process($records, $processors);

        // Ecriture dans le flux de sortie
        $this->getWriter()->export($stream, $records);
    }

    /**
     * Applique une liste de processeurs (RecordProcessor ou DataProcessor) aux enregistrements passés en paramètre.
     *
     * @param Iterable  $records    La liste des enregistrements à traiter.
     * @param array     $processors Les processeurs à exécuter.
     *
     * @return Generator
     */
    protected function process(Iterable $records, array $processors)
    {
        // Applique la liste des processeurs a chaque enregistrement
        foreach ($records as $key => $record) {
            // Si un processeur retourne null, on ignore l'enregistrement
            foreach ($processors as $processor) {
                $record = $processor->process($record);
                if (is_null($record)) {
                    continue 2;
                }
            }

            // Génère l'enregistrement traité
            yield $key => $record;
        }
    }

    /**
     * Convertit les enregistrements passés en paramètres.
     *
     * @param Iterable $records Enregistrement à convertir.
     *
     * @return Generator
     */
    protected function convert(Iterable $records)
    {
        $converter = $this->getConverter();
        foreach ($records as $key => $record) {
            yield $key => $converter->convert($record);
        }
    }
}
