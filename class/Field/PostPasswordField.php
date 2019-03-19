<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\Text;

/**
 * Champ standard "password" : mot de passe pour consulter l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_password".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostPasswordField extends Text
{
    public static function loadSchema()
    {
        return [
            'name' => 'password',
            'label' => __('Mot de passe', 'docalist-data'),
            'description' => __('Mot de passe WordPress requis pour consulter la fiche.', 'docalist-data'),
        ];
    }
}
