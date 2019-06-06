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
use Docalist\Type\Collection;

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

        // Génère le mapping de chaque champ
        foreach ($this->record->getSchema()->getFieldNames() as $name) {
            // Récupère le champ
            $field = $this->record->__get($name);

            // Si le champ n'est pas indexable, on ignore
            if (!$field instanceof Indexable) {
                continue;
            }

            // Génère le mapping du champ et fusionne avec le mapping résultat
            $mapping->mergeWith($this->getFieldIndexer($field)->getMapping($field));
        }

        // Retourne le mapping résultat
        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData(): array
    {
        $data = [];
        foreach ($this->record->getFields() as $field) {
            if ($field instanceof Indexable) {
                $data += $this->getFieldIndexer($field)->getIndexData($field);
            }
        }

        return $data;
    }
}
