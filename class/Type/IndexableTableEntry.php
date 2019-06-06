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

namespace Docalist\Data\Type;

use Docalist\Type\TableEntry;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableCollection;
use Docalist\Data\Indexer\TableEntryIndexer;

/**
 * Un TableEntry qui implémente l'interface Indexable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class IndexableTableEntry extends TableEntry implements Indexable
{
    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return TableEntryIndexer::class;
    }
}
