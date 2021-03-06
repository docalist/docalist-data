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
 * Un callable (pour les pipelines de données) qui filtre les données vides.
 *
 * Le filtre retourne null quand empty() retourne true. Les autres données sont retourneés inchangés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FilterEmpty
{
    public function __invoke($data)
    {
        return empty($data) ? null : $data;
    }
}
