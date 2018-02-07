<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Settings;

use Docalist\Data\Settings\DatabaseSettings;

/**
 * Config de Docalist Databases.
 *
 * @property DatabaseSettings[] $databases Liste des bases.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Settings extends \Docalist\Type\Settings
{
    protected $id = 'docalist-data-settings';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'databases' => [
                    'type' => DatabaseSettings::class,
                    'repeatable' => true,
                    'key' => 'name',
                    'label' => __('Liste des bases de données docalist', 'docalist-data'),
                ],
            ],
        ];
    }
}
