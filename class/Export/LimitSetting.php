<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\Integer;

/**
 * Options de configuration du plugin.
 *
 * @property Text       $role   Rôle Wordpress.
 * @property Integer    $limit  Limite pour ce rôle.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LimitSetting extends Composite
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'role' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Rôle WordPress', 'docalist-data'),
                    'description' => __("Nom du groupe d'utilisateurs", 'docalist-data'),
                ],
                'limit' => [
                    'type' => 'Docalist\Type\Integer',
                    'label' => __('Limite pour ce rôle', 'docalist-data'),
                    'description' => __(
                        'Nombre maximum de notices exportables pour ce rôle (0 = pas de limite).',
                        'docalist-data'
                    ),
                ],
            ],
        ];
    }
}
