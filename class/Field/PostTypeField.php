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
 * Champ standard "posttype" : nom de code de la base docalist où est stocké l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_type".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostTypeField extends Text
{
    public static function loadSchema()
    {
        return [
            'name' => 'posttype',
            'label' => __('Type de post WordPress', 'docalist-data'),
            'description' => __('Nom de code interne du type de post WordPress', 'docalist-data'),
        ];
    }
}
