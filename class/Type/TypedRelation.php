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

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Data\Type\Relation;
use Docalist\Data\Record;
use Docalist\Data\Type\Collection\TypedRelationCollection;

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
            'label' => __('Relation', 'docalist-data'),
            'description' => __('Relation vers une autre fiche et type de relation.', 'docalist-data'),
            'fields' => [
                'type' => [
                    'description' => __('Type de relation', 'docalist-data'),
                ],
                'value' => [
                    'type' => Relation::class,
                    'label' => __('Fiche liée', 'docalist-data'),
                    'description' => __('Post ID de la fiche liée', 'docalist-data'),
                ],
            ],
        ];
    }

    /**
     * Retourne l'entité indiquée par la relation.
     *
     * Cette méthode est juste un raccourci pour $this->value->getEntity().
     *
     * @return Record|null L'objet Record correspondant à l'entité liée ou null s'il n'y a pas d'entité liée.
     */
    public function getEntity(): ?Record
    {
        return $this->value->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass()
    {
        return TypedRelationCollection::class;
    }
}
