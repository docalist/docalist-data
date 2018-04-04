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

use Docalist\Data\Export\Exporter\DocalistJson;
use Docalist\Data\Export\Exporter\DocalistXml;

/**
 * Gère la liste des exports prédéfinis de docalist-data.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class StandardExporters
{
    /**
     * Retourne la liste des exports prédéfinis de docalist-data.
     *
     * @return array[] Un tableau de la forme format-name => settings.
     */
    public static function getList(): array
    {
        return [
            DocalistJson::getID()       => DocalistJson::class,
            DocalistJsonPretty::getID() => DocalistJsonPretty::class,
            DocalistXml::getID()        => DocalistXml::class,
            DocalistXmlPretty::getID()  => DocalistXmlPretty::class,
        ];
    }
}
