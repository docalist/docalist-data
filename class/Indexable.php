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

use Docalist\Search\Mapping;

/**
 * Indexable : un objet qui génère des attributs de recherche dans l'index docalist-search.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Indexable
{
    /**
     * Définit les attributs de recherche utilisés dans le mapping passé en paramètre.
     *
     * @param Mapping $mapping Mapping dans lequel générer les attributs de recherche.
     */
    public function buildMapping(Mapping $mapping): void;

    /**
     * Ajoute les données à indexer dans le tableau passé en paramètre.
     *
     * @param array $data Les données qui seront stockées dans l'index (par référence).
     */
    public function buildIndexData(array & $data): void;
}
