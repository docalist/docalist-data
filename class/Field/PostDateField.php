<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\DateTime;

/**
 * Champ standard "creation" : date/heure de création de l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_date".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostDateField extends DateTime
{
    public static function loadSchema()
    {
        return [
            'name' => 'creation',
            'label' => __('Création', 'docalist-data'),
            'description' => __('Date/heure de création de la fiche.', 'docalist-data'),
        ];
    }
}
