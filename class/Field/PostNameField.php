<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\Text;

/**
 * Champ standard "slug" : permalien de l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_name".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostNameField extends Text
{
    public static function loadSchema()
    {
        return [
            'name' => 'slug',
            'label' => __('Slug de la fiche', 'docalist-data'),
            'description' => __('Slug utilisé pour construire le permalien de la fiche', 'docalist-data'),
        ];
    }
}
