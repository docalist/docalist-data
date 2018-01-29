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
use WP_User;

/**
 * Champ standard "createdBy" : login de l'utilisateur WordPress qui a créé l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_author".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostAuthor extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Créé par', 'docalist-data'),
            'description' => __("Nom de login de l'utilisateur WordPress qui a créé la fiche.", 'docalist-data'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $author = get_user_by('id', $this->getPhpValue()); /** @var WP_User $author */

        return $author->display_name;
    }
}
