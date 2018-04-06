<?php declare(strict_types=1);
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
use Docalist\Data\Operation\FilterEmptyArrayElements;
use Docalist\Data\Operation\FilterEmpty;
use Docalist\Data\Operation\SortArrayByKey;
use Docalist\Data\Export\Writer\JsonWriter;

/**
 * Export Docalist au format JSON.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJson extends StandardExporter
{
    public function __construct()
    {
        parent::__construct(new DocalistConverter(), new JsonWriter());
        $this->appendOperation(new FilterEmptyArrayElements());
        $this->appendOperation(new FilterEmpty());
        $this->appendOperation(new SortArrayByKey());
    }

    public static function getID(): string
    {
        return 'docalist-json';
    }

    public static function getLabel(): string
    {
        return __('Docalist JSON', 'docalist-data');
    }

    public static function getDescription(): string
    {
        return __('Fichier JSON contenant les données Docalist en format natif.', 'docalist-data');
    }
}
