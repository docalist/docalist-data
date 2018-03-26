<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Filter;

use Docalist\Data\Filter\Filter;

/**
 * Trie les tableaux par ordre alphabétique des clés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SortArrayByKey implements Filter
{
    public function __invoke($data)
    {
        is_array($data) && ksort($data);

        return $data;
    }
}
