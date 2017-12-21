<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases;

use Docalist\Databases\Settings\TypeSettings;
use Docalist\Search\IndexManager;
use Docalist\Search\Indexer\CustomPostTypeIndexer;

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
            __('Bases Docalist', 'docalist-databases') // Catégorie
        );
    }

    public function buildIndexSettings(array $settings)
    {
        $types = $this->database->settings()->types;
        foreach ($types as $type) {  /** @var TypeSettings $type */
            $class = Database::getClassForType($type->name());
            $ref = new $class();
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

    protected function map($ref) /** @var Type $ref */
    {
        $document = $ref->map();
//      $document['database'] = $this->database->postType(); // mapping créé dans Type::buildIndexSettings()
        $document['in'] = $this->getCollection(); // mapping créé dans Type::buildIndexSettings()

        return $document;
    }
}
