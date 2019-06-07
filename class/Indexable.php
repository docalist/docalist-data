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

namespace Docalist\Data;

/**
 * Indexable : un objet qui a un indexeur.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Indexable
{
    /**
     * Retourne le nom de la classe PHP à utiliser comme indexeur.
     *
     * @return Indexer
     */
    public function getIndexerClass(): string;
}
