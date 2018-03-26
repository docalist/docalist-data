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
 * Filtre les données vides.
 *
 * Le filtre retourne null quand empty() retourne true. Les autres données sont retourneés inchangés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FilterEmpty implements Filter
{
    public function __invoke($data)
    {
        return empty($data) ? null : $data;
    }
}
