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

use Docalist\Data\Type\Indexable\IndexableTableEntry as RealIndexableTableEntry;

// compat en attendant que le modifs sur les indexeurs soient committées.
class IndexableTableEntry extends RealIndexableTableEntry
{
}
