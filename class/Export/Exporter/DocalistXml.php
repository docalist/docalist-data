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
use Docalist\Data\Export\Writer\XmlWriter;

/**
 * Export Docalist au format XML.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistXml extends StandardExporter
{
    public function __construct()
    {
        parent::__construct(new DocalistConverter(), new XmlWriter());
        $this->appendOperation(new FilterEmptyArrayElements());
        $this->appendOperation(new FilterEmpty());
        $this->appendOperation(new SortArrayByKey());
    }

    public static function getID(): string
    {
        return 'docalist-xml';
    }

    public static function getLabel(): string
    {
        return __('Docalist XML', 'docalist-data');
    }

    public static function getDescription(): string
    {
        return __('Fichier XML contenant les données Docalist en format natif.', 'docalist-data');
    }
}
