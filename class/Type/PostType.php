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

use Docalist\Type\Text;

/**
 * Le PostType WordPress de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostType extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Type de post WordPress', 'docalist-databases'),
            'description' => __('Nom de code interne du type de post WordPress', 'docalist-databases'),
        ];
    }
}
