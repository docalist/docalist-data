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

use Docalist\Data\Settings\TypeSettings;
use Docalist\Search\IndexManager;
use Docalist\Search\Indexer\CustomPostTypeIndexer;
use Docalist\Data\Record;

/**
 * Un indexeur pour les notices d'une base.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DatabaseIndexer extends CustomPostTypeIndexer
{
    /**
     * La base de données indexée.
     *
     * @var Database
     */
    protected $database;

    /**
     * Construit l'indexeur.
     *
     * @param Database $database La base à indexer.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        parent::__construct(
            $database->postType(),                  // Nom du post type
            $this->database->settings()->name(),    // collection (in:) = nom de la base
            __('Bases Docalist', 'docalist-data') // Catégorie
        );
    }

    public function buildIndexSettings(array $settings)
    {
        $types = $this->database->settings()->types;
        foreach ($types as $type) {  /* @var TypeSettings $type */
            $ref = $this->database->createReference($type->name(), []);
            $settings = $ref->buildIndexSettings($settings, $this->database);
        }

        return $settings;
    }

    protected function index($post, IndexManager $indexManager)
    {
        $ref = $this->database->fromPost($post);
        $esType = $this->database->postType() . '-' . $ref->type();

        $indexManager->index($this->getType(), $this->getID($post), $this->map($ref), $esType);
    }

    protected function remove($post, IndexManager $indexManager)
    {
        $ref = $this->database->fromPost($post);
        $esType = $this->database->postType() . '-' . $ref->type();

        $indexManager->delete($this->getType(), is_scalar($post) ? $post : $this->getID($post), $esType);
    }

    protected function map($ref) /* @var Record $ref */
    {
        $document = $ref->map();
//      $document['database'] = $this->database->postType(); // mapping créé dans Type::buildIndexSettings()
        $document['in'] = $this->getCollection(); // mapping créé dans Type::buildIndexSettings()

        return $document;
    }
}
