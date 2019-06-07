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

use Docalist\Type\Any;
use Docalist\Search\Mapping;
use Docalist\Forms\Container;

/**
 * Interface d'un indexeur.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Indexer
{
    /**
     * Retourne le formulaire à utiliser pour modifier les paramètres d'indexation.
     *
     * @return Container Un formulaire.
     */
    public function getIndexSettingsForm(): Container;

    /**
     * Retourne le mapping à utiliser pour créer l'index docalist-search.
     *
     * @return Mapping
     */
    public function getMapping(): Mapping;

    /**
     * Retourne les données qui seront stockées dans l'index docalist-search.
     *
     * @return array
     */
    public function getIndexData(): array;
}
