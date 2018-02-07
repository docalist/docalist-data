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

use Docalist\Type\Settings as TypeSettings;
use Docalist\Type\Integer;
use Docalist\Data\Export\LimitSetting;

/**
 * Options de configuration du plugin.
 *
 * @property Integer        $exportpage ID de la page "export".
 * @property LimitSetting   $limit      Limites de l'export.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Settings extends TypeSettings
{
    protected $id = 'docalist-data-export';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'exportpage' => [
                    'type' => Integer::class,
                    'label' => __("Page pour l'export", 'docalist-data'),
                    'description' => __(
                        "Page WordPress sur laquelle l'export sera disponible.",
                        'docalist-data'
                    ),
                    'default' => 0,
                ],
                'limit' => [
                    'type' => LimitSetting::class,
                    'repeatable' => true,
                    'label' => __("Limites de l'export", 'docalist-data'),
                    'description' => __(
                        'Liste des rôles autorisés à exporter des notices et nombre maximum de notices par rôle.',
                        'docalist-data'
                    ),
                    'key' => 'role',
                ],
            ],
        ];
    }
}
