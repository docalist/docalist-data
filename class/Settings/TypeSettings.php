<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Settings;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Schema\Schema;

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
 * @property Text $name Nom du type (article, book, degree...)
 * @property Text $label Libellé.
 * @property Text $description Description.
 * @property Schema[] $grids Grilles de saisie et d'affichage.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypeSettings extends Composite
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'name' => [ // article, book, etc.
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Nom du type', 'docalist-databases'),
                    'description' => __('Nom de code utilisé en interne pour désigner le type.', 'docalist-databases'),
                ],

                'label' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Libellé du type', 'docalist-databases'),
                    'description' => __('Libellé utilisé pour désigner ce type.', 'docalist-databases'),
                ],

                'description' => [
                    'type' => 'Docalist\Type\LargeText',
                    'label' => __('Description', 'docalist-databases'),
                    'description' => __('Description du type.', 'docalist-databases'),
                ],

                // helpurl -> lien vers page qui décrit le type
                // droits ?

                'grids' => [
                    'type' => 'Docalist\Databases\Grid*',
                    'key' => 'name', // edit, display-full, display-short, ...
                    'label' => __('Grilles et formulaires', 'docalist-databases'),
                    'description' => __("Grilles de saisie et d'affichage pour ce type.", 'docalist-databases'),
                ],
            ],
        ];
    }
}
