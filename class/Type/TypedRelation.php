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

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Databases\Type\Relation;

/**
 * Une relation typée : un type composite associant un type provenant d'une table d'autorité à un champ de type
 * Relation.
 *
 * @property TableEntry $type   Type    Type de relation.
 * @property Relation   $value  Value   Post ID de la fiche liée.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypedRelation extends TypedText
{
    public static function loadSchema()
    {
        return [
            'label' => __('Relation', 'docalist-databases'),
            'description' => __('Relation vers une autre fiche et type de relation.', 'docalist-databases'),
            'fields' => [
                'type' => [
                    'table' => 'table:relations',
                    'description' => __('Type de relation', 'docalist-databases'),
                ],
                'value' => [
                    'type' => 'Docalist\Databases\Type\Relation',
                    'label' => __('Fiche liée', 'docalist-databases'),
                    'description' => __('Post ID de la fiche liée', 'docalist-databases'),
                ],
            ],
        ];
    }
}
