<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Type;

use Docalist\Type\Text;

/**
 * ID WordPress du post parent de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostParent extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Notice parent', 'docalist-data'),
            'description' => __('ID WordPress du post parent de la notice.', 'docalist-data'),
        ];
    }
}
