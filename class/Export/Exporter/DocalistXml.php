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

use Docalist\Data\Export\Exporter\StandardExporter;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Export\DataProcessor\RemoveEmptyFields;
use Docalist\Data\Export\DataProcessor\RemoveEmptyRecords;
use Docalist\Data\Export\DataProcessor\SortFields;
use Docalist\Data\Export\Writer\XmlWriter;

/**
 * Export Docalist au format XML.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistXml extends StandardExporter
{
    /**
     * Initialise l'exporteur.
     */
    public function __construct()
    {
        parent::__construct(new DocalistConverter(), new XmlWriter());
        $this->addDataProcessor(new RemoveEmptyFields());
        $this->addDataProcessor(new RemoveEmptyRecords());
        $this->addDataProcessor(new SortFields());
    }

    public static function getID()
    {
        return 'docalist-xml';
    }

    public static function getLabel()
    {
        return __('Docalist XML', 'docalist-data');
    }

    public static function getDescription()
    {
        return __('Fichier XML contenant les données Docalist en format natif.', 'docalist-data');
    }
}
