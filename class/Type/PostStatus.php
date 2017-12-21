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
 * Le statut wordpress de la notice.
 */
class PostStatus extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Statut WordPress', 'docalist-databases'),
            'description' => __('Statut de la fiche.', 'docalist-databases'),
        ];
    }
}
