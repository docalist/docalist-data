<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Settings;

/**
 * Config de Docalist Databases.
 *
 * @property DatabaseSettings[] $databases Liste des bases.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Settings extends \Docalist\Type\Settings
{
    protected $id = 'docalist-databases-settings';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'databases' => [
                    'type' => 'Docalist\Databases\Settings\DatabaseSettings*',
                    'key' => 'name',
                    'label' => __('Liste des bases de données documentaires', 'docalist-databases'),
                ],
            ],
        ];
    }
}
