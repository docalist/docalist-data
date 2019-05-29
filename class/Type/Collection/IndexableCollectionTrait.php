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
use Docalist\Search\Mapping;
use InvalidArgumentException;

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
    public function buildMapping(Mapping $mapping): void
    {
        // Crée un élément temporaire
        $item = $this->createTemporaryItem();

        // Demande à l'élément de génèrer le mapping (génère une exception s'il n'est pas Indexable)
        $item->buildMapping($mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function buildIndexData(array & $data): void
    {
        // Indexe chacun des items de la collection (génère une exception si les items ne sont pas Indexable)
        foreach ($this->phpValue as $item) { /** @var Indexable $item */
            $item->buildIndexData($data);
        }
    }
}
