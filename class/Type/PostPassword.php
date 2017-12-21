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
 * Mot de passe WordPress requis pour accéder à la fiche.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostPassword extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Mot de passe', 'docalist-databases'),
            'description' => __('Mot de passe WordPress requis pour consulter la fiche.', 'docalist-databases'),
        ];
    }
}
