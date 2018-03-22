<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Transformer\ArrayTransformer;

use Docalist\Data\Transformer\ArrayTransformer;

/**
 * Filtre les tableaux complètement vides.
 *
 * Le filtre retourne null pour les tableaux pour lesquels empty() retourne true. Les autres tableaux
 * sont retournés inchangés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FilterEmptyArrays implements ArrayTransformer
{
    public function transform(array $data)
    {
        return empty($data) ? null : $data;
    }
}
