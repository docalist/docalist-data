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

use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Type\Collection;
use Docalist\Data\Type\TypedRelation;

/**
 * Une collection d'objets TypedRelation.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypedRelationCollection extends IndexableTypedValueCollection
{
    /**
     * Filtre les éléments de la collection sur le champ type des éléments et retourne l'entité associée.
     *
     * @param array $include    Liste des éléments à inclure (liste blanche) : si le tableau n'est pas vide, seuls les
     *                          éléments indiqués seront retournés.
     *
     * @param array $exclude    Liste des éléments à exclure (liste noire) : si le tableau n'est pas vide, les
     *                          éléments indiqués seront supprimés de la collection retournée.
     *
     * @param int   $limit      Nombre maximum d'éléments à retourner (0 = pas de limite).
     *
     * @return Collection Une collection d'objets Record.
     */
    final public function filterEntities(array $include = [], array $exclude = [], int $limit = 0): Collection
    {
        // Détermine la liste des éléments à retourner
        $items = [];
        foreach ($this->phpValue as $item) { /** @var TypedRelation $item */
            // Filtre les eléments
            if (is_null($item = $this->filterItem($item, $include, $exclude))) {
                continue;
            }

            // Ajoute la valeur de l'élément à la liste
            $items[] = $item->value->getEntity();

            // On s'arrête quand la limite est atteinte
            if ($limit && count($items) >= $limit) {
                break;
            }
        }

        // Crée une nouvelle collection contenant les éléments obtenus
        $result = new Collection([], $this->getSchema()); // les éléments qu'on retourne ne sont plus des TypedValue
        $result->phpValue = $items;

        // Ok
        return $result;
    }
}
