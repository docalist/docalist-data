<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\ListEntry;
use WP_User;

/**
 * Champ standard "createdBy" : ID de l'utilisateur WordPress qui a créé l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_author".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostAuthorField extends ListEntry
{
    public static function loadSchema()
    {
        return [
            'name' => 'createdBy',
            'label' => __('Créé par', 'docalist-data'),
            'description' => __("ID de l'utilisateur WordPress qui a créé la fiche.", 'docalist-data'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $author = get_user_by('id', $this->getPhpValue()); /* @var WP_User $author */

        return $author->display_name;
    }

    /**
     * Retourne la liste des utilisateurs WordPress.
     *
     * @return array Un tableau de la forme [login => display_name].
     */
    protected function getEntries()
    {
        $result = [];
        foreach (get_users() as $user) { /** @var WP_User $user */
            $result[$user->ID] = $user->display_name . ' (' . $user->user_login . ')';
        }

        return $result;
    }
}
