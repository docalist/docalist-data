<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Type;

use Docalist\Type\Text;

/**
 * Le slug WordPress de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostSlug extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Slug de la fiche', 'docalist-data'),
            'description' => __('Slug utilisé pour construire le permalien de la fiche', 'docalist-data'),
        ];
    }
}
