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

use Docalist\Type\Text;

/**
 * Le slug WordPress de la notice.
 */
class PostSlug extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Slug de la fiche', 'docalist-databases'),
            'description' => __('Slug utilisé pour construire le permalien de la fiche', 'docalist-databases'),
        ];
    }
}
