<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Data\Field;

use Docalist\Type\ListEntry;
use Docalist\Data\Database;

/**
 * Champ docalist standard "type" : type de l'enregistrement.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypeField extends ListEntry
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'type',
            'label' => __('Type de fiche', 'docalist-data'),
            'description' => __('Type docalist de la fiche.', 'docalist-data'),
        ];
    }

    /**
     * Retourne la liste des types docalist disponibles.
     *
     * @return array Un tableau de la forme [Nom du type => Libellé du type]
     *
     * Remarque : le tableau retourné contient les libellés par défaut des types docalist, pas ceux qui ont été
     * définis par l'utilisateur dans les paramètres des bases docalist.
     */
    protected function getEntries()
    {
        static $types = null;

        // Initialise la liste des types disponibles lors du premier appel
        if (is_null($types)) {
            // Récupère les types disponibles (tableau de la forme type => classe php)
            $types = Database::getAvailableTypes();

            // Détermine le libellé de chaque type
            foreach ($types as $type => $class) {
                $types[$type] = $class::getDefaultSchema()->label();
            }
        }

        // Ok
        return $types;
    }
}
