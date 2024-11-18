<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Data\Export\Settings;

use Docalist\Repository\Repository;
use Docalist\Type\Collection;
use Docalist\Type\Settings;
use Docalist\Type\Integer as DocalistInteger;
use Docalist\Data\Export\Settings\LimitSetting;

/**
 * Options de configuration du module d'export.
 *
 * @property DocalistInteger        $exportpage ID de la page "export".
 * @property Collection<LimitSetting>   $limit      Limites de l'export.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ExportSettings extends Settings
{
    public function __construct(Repository $repository)
    {
        parent::__construct($repository, 'docalist-data-export');
    }

    public static function loadSchema(): array
    {
        return [
            'fields' => [
                'exportpage' => [
                    'type' => DocalistInteger::class,
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
