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

use Docalist\Type\Collection;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableCollectionTrait;

/**
 * Une Collection indexable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class IndexableCollection extends Collection implements Indexable
{
    use IndexableCollectionTrait;
}
