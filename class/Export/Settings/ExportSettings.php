<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Settings;

use Docalist\Type\Settings;
use Docalist\Type\Integer;
use Docalist\Data\Export\Settings\LimitSetting;

/**
 * Options de configuration du module d'export.
 *
 * @property Integer        $exportpage ID de la page "export".
 * @property LimitSetting   $limit      Limites de l'export.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ExportSettings extends Settings
{
    protected $id = 'docalist-data-export';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'exportpage' => [
                    'type' => Integer::class,
                    'label' => __("Page WordPress", 'docalist-data'),
                    'description' => __("Page WordPress sur laquelle l'export sera disponible.", 'docalist-data'),
                    'default' => 0,
                ],
                'limit' => [
                    'type' => LimitSetting::class,
                    'repeatable' => true,
                    'label' => __("Utilisateurs", 'docalist-data'),
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
