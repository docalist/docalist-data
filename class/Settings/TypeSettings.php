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

namespace Docalist\Data\Settings;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\LargeText;
use Docalist\Data\Grid;

/**
 * Les paramètres d'un type au sein d'une base de données.
 *
 * Chaque type contient un nom qui indique le type de notice (article,
 * book, degree...) et des grilles (des schémas) qui sont utilisées
 * pour l'édition et l'affichage des notices de ce type.
 *
 * La grille de saisie a un nom particulier 'edit'. Toutes les autres
 * grilles sont des formats d'affichage.
 *
 * @property Text       $name           Nom du type (article, book, degree...)
 * @property Text       $label          Libellé.
 * @property LargeText  $description    Description.
 * @property Grid[]     $grids          Grilles de saisie et d'affichage.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypeSettings extends Composite
{
    public static function loadSchema(): array
    {
        return [
            'fields' => [
                'name' => [ // article, book, etc.
                    'type' => Text::class,
                    'label' => __('Nom du type', 'docalist-data'),
                    'description' => __('Nom de code utilisé en interne pour désigner le type.', 'docalist-data'),
                ],

                'label' => [
                    'type' => Text::class,
                    'label' => __('Libellé du type', 'docalist-data'),
                    'description' => __('Libellé utilisé pour désigner ce type.', 'docalist-data'),
                ],

                'description' => [
                    'type' => LargeText::class,
                    'label' => __('Description', 'docalist-data'),
                    'description' => __('Description du type.', 'docalist-data'),
                ],

                // helpurl -> lien vers page qui décrit le type
                // droits ?

                'grids' => [
                    'type' => Grid::class,
                    'repeatable' => true,
                    'key' => 'name', // edit, display-full, display-short, ...
                    'label' => __('Grilles et formulaires', 'docalist-data'),
                    'description' => __("Grilles de saisie et d'affichage pour ce type.", 'docalist-data'),
                ],
            ],
        ];
    }
}
