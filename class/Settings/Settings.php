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

namespace Docalist\Data\Settings;

use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Repository\Repository;

/**
 * Config de Docalist Databases.
 *
 * @property DatabaseSettings[] $databases Liste des bases.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Settings extends \Docalist\Type\Settings
{
    public function __construct(Repository $repository)
    {
        parent::__construct($repository, 'docalist-data-settings');
    }

    public static function loadSchema(): array
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
