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

use Docalist\Data\Type\Collection\TopicCollection;
use Docalist\Data\Type\Collection\IndexableCollectionTrait;
use Docalist\Data\Indexable;

/**
 * Une TopicCollection indexable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class IndexableTopicCollection extends TopicCollection implements Indexable
{
    use IndexableCollectionTrait;
}
