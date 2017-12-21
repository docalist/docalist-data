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
 * La date de dernière modification de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostModified extends DateTime
{
    public static function loadSchema()
    {
        return [
            'label' => __('Dernière modification', 'docalist-databases'),
            'description' => __('Date/heure de dernière modification de la fiche.', 'docalist-databases'),
        ];
    }
}
