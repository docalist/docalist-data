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

use Docalist\Type\Text;

/**
 * Champ standard "parent" : ID de l'enregistrement parent.
 *
 * Ce champ Docalist correspond au champ WordPress "post_parent".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostParentField extends Text
{
    public static function loadSchema()
    {
        return [
            'name' => 'parent',
            'label' => __('Notice parent', 'docalist-data'),
            'description' => __('ID WordPress du post parent de la notice.', 'docalist-data'),
        ];
    }
}
