<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Type;

use Docalist\Type\DateTime;

/**
 * La date de création de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
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
