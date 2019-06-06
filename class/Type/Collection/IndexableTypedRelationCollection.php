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

namespace Docalist\Data\Type\Collection;

use Docalist\Data\Type\Collection\TypedRelationCollection;
use Docalist\Data\Type\Collection\IndexableCollectionTrait;
use Docalist\Data\Indexable;

/**
 * Une TypedRelationCollection indexable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class IndexableTypedRelationCollection extends TypedRelationCollection implements Indexable
{
    use IndexableCollectionTrait;
}
