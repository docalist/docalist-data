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

use Docalist\Data\Export\Exporter\DocalistXml;

/**
 * Export Docalist au format XML (formatté et indenté).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistXmlPretty extends DocalistXml
{
    public static function getID()
    {
        return parent::getId() . '-pretty';
    }

    public static function getLabel()
    {
        return parent::getLabel() . __(' (formatté et indenté)', 'docalist-data');
    }

    public static function getDescription()
    {
        return parent::getDescription() .
            __(' Le fichier généré est formatté pour être plus facilement lisible.', 'docalist-data');
    }

    protected function initWriter()
    {
        return parent::initWriter()->setIndent(4);
    }
}
