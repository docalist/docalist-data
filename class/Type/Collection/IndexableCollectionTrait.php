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

use Docalist\Data\Indexable;

/**
 * Un trait qui permet à une Collection d'implémenter l'interface Indexable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
trait IndexableCollectionTrait // implements Indexable
{
    /**
     * {@inheritdoc}
     */
    public function getIndexerClass(): string
    {
        $item = $this->createTemporaryItem(); /** @var Indexable $item */

        return $item->getIndexerClass();
    }
}
