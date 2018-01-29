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
 * Champ standard "posttitle" : titre de l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_title".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostTitle extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Titre du post', 'docalist-data'),
            'description' => __('Titre du post WordPress.', 'docalist-data'),
        ];
    }

    public function getDefaultEditor()
    {
        return 'input-large';
    }
}
