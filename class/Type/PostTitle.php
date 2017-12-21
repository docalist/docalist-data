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
 * Le titre WordPress de la fiche.
 */
class PostTitle extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Titre du post', 'docalist-databases'),
            'description' => __('Titre du post WordPress.', 'docalist-databases'),
        ];
    }

    public function getDefaultEditor()
    {
        return 'input-large';
    }
}
