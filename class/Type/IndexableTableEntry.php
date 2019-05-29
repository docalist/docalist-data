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

namespace Docalist\Data\Type;

use Docalist\Type\TableEntry;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableCollection;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Data\HasIndexSettings;

/**
 * Un TableEntry qui implémente les interfaces Indexable et HasIndexSettings.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class IndexableTableEntry extends TableEntry implements Indexable, HasIndexSettings
{
    /**
     * Nom du champ de recherche.
     *
     * @var string
     */
    public const SEARCH_FIELD = 'field-name';

    /**
     * Nom du filtre.
     *
     * @var string
     */
    public const LABEL_FILTER = 'filter.field-name.label';

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexSettingsForm(): Container
    {
        $form = new Container();

        // Champ de recherche
        $form->checkbox()
            ->setName('search-field')
            ->setLabel(static::SEARCH_FIELD)
            ->setDescription(__('Recherche sur le code et le libellé des entrées.', 'docalist-data'));

        // Filtre / facette
        $form->checkbox()
            ->setName('label-filter')
            ->setLabel(static::LABEL_FILTER)
            ->setDescription(__('Filtre sur le libellé des entrées.', 'docalist-data'));

        return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function buildMapping(Mapping $mapping): void
    {
        // Champ de recherche
        if ($this->getOption('search-field')) {
            $mapping
                ->text(static::SEARCH_FIELD)
                ->setFeatures(Mapping::FULLTEXT)
                ->setLabel(sprintf(
                    __(
                        'Recherche sur le champ "%s" des références docalist.',
                        'docalist-data'
                    ),
                    $this->getSchema()->name()
                ))
                ->setDescription(sprintf(
                    __(
                        'Pour chacune des entrées du champ "%s", contient à la fois le code et le libellé
                        qui figure dans la table d\'autorité associée au champ.',
                        'docalist-data'
                    ),
                    $this->getSchema()->name()
                ));
        }

        // Filtre/facette
        if ($this->getOption('label-filter')) {
            $mapping
                ->keyword(static::LABEL_FILTER)
                ->setFeatures(Mapping::AGGREGATE | Mapping::FILTER)
                ->setLabel(sprintf(
                    __(
                        'Filtre sur le champ "%s" des références docalist.',
                        'docalist-data'
                    ),
                    $this->getSchema()->name()
                ))
                ->setDescription(sprintf(
                    __(
                        'Cet attribut est un filtre sur le libellé des entrées qui figurent dans le champ "%s"
                        des références docalist. Les libellés indexés sont ceux qui figurent dans la table
                        d\'autorité associée au champ.',
                        'docalist-data'
                    ),
                    $this->getSchema()->name()
                ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function buildIndexData(array & $data): void
    {
        if (empty($code = $this->getPhpValue())) {
            return;
        }
        $label = $this->getEntryLabel();

        // Champ de recherche
        if ($this->getOption('search-field')) {
            $data[static::SEARCH_FIELD][] = $code;
            $data[static::SEARCH_FIELD][] = $label;
        }

        // Filtre/facette
        if ($this->getOption('label-filter')) {
            $data[static::LABEL_FILTER][] = $label;
        }
    }
}
