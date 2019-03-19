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

namespace Docalist\Data\Operation;

/**
 * Un callable (pour les pipelines de données) qui trie les tableaux par ordre alphabétique des clés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SortArrayByKey
{
    public function __invoke(array $data)
    {
        ksort($data);

        return $data;
    }
}
