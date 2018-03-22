<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Transform\ArrayTransformer;

use Docalist\Data\Transform\ArrayTransformer;

/**
 * Trie les tableaux par ordre alphabétique des clés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SortByKey implements ArrayTransformer
{
    public function transform(array $data)
    {
        ksort($data);

        return $data;
    }
}
