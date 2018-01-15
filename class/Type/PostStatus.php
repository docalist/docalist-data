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
 * Le statut wordpress de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostStatus extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Statut WordPress', 'docalist-data'),
            'description' => __('Statut de la fiche.', 'docalist-data'),
        ];
    }
}
