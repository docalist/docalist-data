<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\Text;

/**
 * Champ standard "status" : statut de visibilité de l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_status".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostStatusField extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Statut WordPress', 'docalist-data'),
            'description' => __('Statut de la fiche.', 'docalist-data'),
        ];
    }
}
