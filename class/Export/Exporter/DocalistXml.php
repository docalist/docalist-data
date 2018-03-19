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

use Docalist\Data\Export\Exporter\DocalistJson;
use Docalist\Data\Export\Writer\XmlWriter;

/**
 * Export Docalist au format XML.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistXml extends DocalistJson
{
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

    protected function initWriter()
    {
        return new XmlWriter();
    }
}
