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

namespace Docalist\Data\Indexer;

use Docalist\Data\Indexer;
use Docalist\Data\Record;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Data\Indexable;
use Docalist\Search\Indexer\Field\PostStatusIndexer;
use Docalist\Search\Indexer\Field\PostTitleIndexer;
use Docalist\Search\Indexer\Field\PostDateIndexer;
use Docalist\Search\Indexer\Field\PostModifiedIndexer;
use Docalist\Search\Indexer\Field\PostAuthorIndexer;
use Docalist\Search\Indexer\Field\PostTypeIndexer;

/**
 * Indexeur standard pour un Record.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RecordIndexer implements Indexer
{
    /**
     * Le record à indexer.
     *
     * @var Record
     */
    private $record;

    /**
     * Initialise l'indexeur.
     *
     * @param Record $record Le record à indexer.
     */
    public function __construct(Record $record)
    {
        $this->record = $record;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexSettingsForm(): Container
    {
        return (new Container())
            ->setLabel(__("Options d'indexation", 'docalist-data'))
            ->setDescription(__('Non utilisé pour les records', 'docalist-data'));
    }

    /**
     * Retourne l'indexeur à utiliser pour le champ passé en paramètre.
     *
     * @param Indexable $field
     *
     * @return Indexer
     */
    private function getFieldIndexer(Indexable $field): Indexer
    {
        $class = $field->getIndexerClass();

        return new $class($field);
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(): Mapping
    {
        // Crée le mapping résultat
        $mapping = new Mapping(self::class);

        // Génère le mapping des champs WordPress
        PostTypeIndexer::buildMapping($mapping);
        PostStatusIndexer::buildMapping($mapping);
        PostTitleIndexer::buildMapping($mapping);
        PostDateIndexer::buildMapping($mapping);
        PostAuthorIndexer::buildMapping($mapping);
        PostModifiedIndexer::buildMapping($mapping);

        // à voir : password, parent, slug

        // Génère le mapping des champs docalist
        foreach ($this->record->getSchema()->getFieldNames() as $name) {
            // Récupère le champ
            $field = $this->record->__get($name);

            // Si le champ n'est pas indexable, on ignore
            if (!$field instanceof Indexable) {
                continue;
            }

            // Génère le mapping du champ et fusionne avec le mapping résultat
            $mapping->mergeWith($this->getFieldIndexer($field)->getMapping());
        }

        // Retourne le mapping résultat
        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData(): array
    {
        // Initialise le tableau résultat
        $data = [];

        // Indexe les champs WordPress
        PostTypeIndexer::buildIndexData(
            $this->record->type->getPhpValue(),
            $this->record->getSchema()->label(),
            $data
        );

        PostStatusIndexer::buildIndexData($this->record->status->getPhpValue(), $data);
        PostTitleIndexer::buildIndexData($this->record->posttitle->getPhpValue(), $data);
        PostDateIndexer::buildIndexData($this->record->creation->getPhpValue(), $data);
        PostAuthorIndexer::buildIndexData((int) $this->record->createdBy->getPhpValue(), $data);
        PostModifiedIndexer::buildIndexData($this->record->lastupdate->getPhpValue(), $data);

        // Indexe les champs de la notice
        foreach ($this->record->getFields() as $field) {
            if ($field instanceof Indexable) {
                $data += $this->getFieldIndexer($field)->getIndexData();
            }
        }

        // Ok
        return $data;
    }
}
