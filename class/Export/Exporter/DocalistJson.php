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
use Docalist\Data\Export\Writer\JsonWriter;

/**
 * Export Docalist au format JSON.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJson extends StandardExporter
{
    public static function getID()
    {
        return 'docalist-json';
    }

    public static function getLabel()
    {
        return __('Docalist JSON', 'docalist-data');
    }

    public static function getDescription()
    {
        return __('Fichier JSON contenant les données Docalist en format natif.', 'docalist-data');
    }

    protected function initConverter()
    {
        return new DocalistConverter();
    }

    protected function initDataProcessors()
    {
        return [
            new RemoveEmptyFields(),
            new RemoveEmptyRecords(),
            new SortFields(),
        ];
    }

    protected function initWriter()
    {
        return new JsonWriter();
    }
}
