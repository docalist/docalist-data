<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export;

use Docalist\Data\Export\Docalist;
use Docalist\Data\Export\Json;
use Docalist\Data\Export\Xml;

/**
 * Gère la liste des formats d'export prédéfinis de docalist-data.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PredefinedExportFormats
{
    /**
     * Retourne la liste des formats d'export prédéfinis de docalist-data.
     *
     * @return array[] Un tableau de la forme format-name => settings.
     */
    public static function getList()
    {
        $formats = [];

        $formats['docalist-json'] = [
            'label' => 'Docalist JSON',
            'description' => __(
                'Fichier JSON compact, contenus au format natif de Docalist.',
                'docalist-data'
            ),
            'converter' => Docalist::class,
            'exporter' => Json::class,
        ];

        $formats['docalist-json-pretty'] = [
            'label' => 'Docalist JSON formatté',
            'description' => __(
                'Fichier JSON formatté et indenté, contenus au format natif de Docalist.',
                'docalist-data'
            ),
            'converter' => Docalist::class,
            'exporter' => Json::class,
            'exporter-settings' => [
                'pretty' => true,
            ],
        ];

        $formats['docalist-xml'] = [
            'label' => 'Docalist XML',
            'description' => __(
                'Fichier XML compact, contenus au format natif de Docalist.',
                'docalist-data'
            ),
            'converter' => Docalist::class,
            'exporter' => Xml::class,
        ];

        $formats['docalist-xml-pretty'] = [
            'label' => 'Docalist XML formatté',
            'description' => __(
                'Fichier XML formatté et indenté, contenu au format natif de Docalist.',
                'docalist-data'
            ),
            'converter' => Docalist::class,
            'exporter' => Xml::class,
            'exporter-settings' => [
                'indent' => 4,
            ],
        ];

        return $formats;
    }
}
