<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Export;

use Docalist\Type\Settings as TypeSettings;
use Docalist\Type\Integer;

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
    protected $id = 'docalist-databases-export';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'exportpage' => [
                    'type' => 'Docalist\Type\Integer',
                    'label' => __("Page pour l'export", 'docalist-databases'),
                    'description' => __(
                        "Page WordPress sur laquelle l'export sera disponible.",
                        'docalist-databases'
                    ),
                    'default' => 0,
                ],
                'limit' => [
                    'type' => 'Docalist\Databases\Export\LimitSetting*',
                    'label' => __("Limites de l'export", 'docalist-databases'),
                    'description' => __(
                        'Liste des rôles autorisés à exporter des notices et nombre maximum de notices par rôle.',
                        'docalist-databases'
                    ),
                    'key' => 'role',
                ],
            ],
        ];
    }
}
