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

use Docalist\Search\Indexer\CustomPostTypeIndexer;
use Docalist\Search\Mapping;
use WP_Post;

/**
 * Un indexeur pour les notices d'une base Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class DatabaseIndexer extends CustomPostTypeIndexer
{
    /**
     * La base de données indexée.
     *
     * @var Database
     */
    protected $database;

    /**
     * Initialise l'indexeur.
     *
     * @param Database $database La base docalist à indexer.
     */
    final public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * {@inheritDoc}
     */
    final public function getType(): string
    {
        return $this->database->getPostType();
    }

    /**
     * {@inheritDoc}
     */
    final public function getCollection(): string
    {
        return $this->database->getSettings()->name->getPhpValue();
    }

    /**
     * {@inheritDoc}
     */
    final public function getLabel(): string
    {
        return $this->database->getSettings()->label->getPhpValue();
    }

    /**
     * {@inheritDoc}
     */
    final public function getCategory(): string
    {
        return __('Bases Docalist', 'docalist-data');
    }

    /**
     * {@inheritDoc}
     *
     * Pour une base docalist, le mapping est généré en fusionnant les mappings de chaque type
     * de notice présent dans la base.
     */
    final public function getMapping(): Mapping
    {
        // Crée le mapping des champs WordPress
        $result = parent::getMapping();

        // Construit le mapping de chaque type de notices
        foreach ($this->database->getSettings()->types->keys() as $type) {
            // Crée un enregistrement docalist de ce type
            $record = $this->database->createReference($type, []);

            // Initialise son mapping
            $mapping = new Mapping($this->getCollection() . '-' . $type);

            // Ajoute les champs de la notice
            $record->buildMapping($mapping);

            // Fusionne le mapping obtenu dans le mapping résultat
            $result->mergeWith($mapping);
        }

        // Ok
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    final public function getIndexData(WP_Post $post): array
    {
        // Indexe les champs WordPress
        $indexData = parent::getIndexData($post);

        // Convertit le post en enregistrement docalist
        $record = $this->database->fromPost($post);

        // Ajoute les données de la notice
        $record->buildIndexData($indexData);

        // Ok
        return $indexData;
    }
}
