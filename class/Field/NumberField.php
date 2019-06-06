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

use Docalist\Type\TypedNumber;
use Docalist\Forms\Container;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Data\Indexer\NumberFieldIndexer;

/**
 * Numéros officiels et codes associés à l'entité.
 *
 * Ce champ permet de saisir des codes et des numéros.
 *
 * Chaque numéro comporte deux sous-champs :
 * - `type` : type de numéro,
 * - `value` : code ou numéro.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de numéros disponibles
 * ("table:number-type" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class NumberField extends TypedNumber implements Indexable
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'number',
            'label' => __('Numéros', 'docalist-people'),
            'description' => __('Numéros officiels.', 'docalist-people'),
            'repeatable' => true,
            'fields' => [
                'type' => [
                    'table' => 'table:number-type',
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableTypedValueCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return NumberFieldIndexer::class;
    }
}
