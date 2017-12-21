<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Type;

use Docalist\Type\DateTime;

/**
 * La date de création de la notice.
 */
class PostDate extends DateTime
{
    public static function loadSchema()
    {
        return [
            'label' => __('Création', 'docalist-databases'),
            'description' => __('Date/heure de création de la fiche.', 'docalist-databases'),
        ];
    }
}
