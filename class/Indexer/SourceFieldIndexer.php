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

use Docalist\Data\Indexer\FieldIndexer;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Search\Mapping\Field\Parameter\IndexOptions;
use Docalist\Data\Field\SourceField;

/**
 * Indexeur pour le champ "source".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SourceFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var IndexableTypedValueCollection
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param IndexableTypedValueCollection $field
     */
    public function __construct(IndexableTypedValueCollection $field)
    {
        parent::__construct($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeLabel(string $attribute, string $type = ''): string
    {
        switch ($attribute) {
            case 'search':
                return __(
                    'Recherche sur les provenances qui figurent dans le champ "source" des références docalist.',
                    'docalist-data'
                );

            case 'label-filter':
                return __(
                    'Filtre sur le libellé des provenances qui figurent dans le champ "source"
                    des références docalist.',
                    'docalist-data'
                );

            case 'label-suggest':
                return __(
                    'Autocomplete sur le libellé des provenances qui figurent dans le champ "source"
                    des références docalist.',
                    'docalist-data'
                );
        }

        return parent::getAttributeLabel($attribute, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeDescription(string $attribute, string $type = ''): string
    {
        switch ($attribute) {
            case 'search':
                return __(
                    'Contient les codes et les libellés indiquant la provenance des données.',
                    'docalist-data'
                );

            case 'label-filter':
            case 'label-suggest':
                return __(
                    'Contient les libellés indiquant la source (la provenance) des données.',
                    'docalist-data'
                );
        }

        return parent::getAttributeDescription($attribute, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexSettingsForm(): Container
    {
        $form = parent::getIndexSettingsForm();

        $form->checkbox()
            ->setName('search')
            ->setLabel($this->getAttributeName('search'))
            ->setDescription($this->getAttributeLabel('search'));

        $form->checkbox()
            ->setName('label-filter')
            ->setLabel($this->getAttributeName('label-filter'))
            ->setDescription($this->getAttributeLabel('label-filter'));

        $form->checkbox()
            ->setName('label-suggest')
            ->setLabel($this->getAttributeName('label-suggest'))
            ->setDescription($this->getAttributeLabel('label-suggest'));

        return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(): Mapping
    {
        $mapping = parent::getMapping();

        $attr = $this->getAttributes();

        if (isset($attr['search'])) {
            $mapping
                ->literal($attr['search'])
                ->setFeatures(Mapping::FULLTEXT)
                ->setLabel($this->getAttributeLabel('search'))
                ->setDescription($this->getAttributeDescription('search'));
        }

        if (isset($attr['label-filter'])) {
            $mapping
                ->keyword($attr['label-filter'])
                ->setFeatures(Mapping::AGGREGATE | Mapping::FILTER)
                ->setLabel($this->getAttributeLabel('label-filter'))
                ->setDescription($this->getAttributeDescription('label-filter'));
        }

        if (isset($attr['label-suggest'])) {
            $mapping
                ->suggest($attr['label-suggest'])
                ->setFeatures(Mapping::LOOKUP)
                ->setLabel($this->getAttributeLabel('label-suggest'))
                ->setDescription($this->getAttributeDescription('label-suggest'));
        }

        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData(): array
    {
        // Récupère la liste des attributs à générer
        $attr = $this->getAttributes();

        // Si le champ n'est pas indexé ou que la collection est vide, terminé
        if (empty($attr) || 0 === $this->field->count()) {
            return [];
        }

        // Indexe toutes les entrées
        $data = [];
        foreach ($this->field as $item) { /** @var SourceField $item */
            $code = $item->type->getPhpValue();
            $label = $item->type->getEntryLabel();

            if (isset($attr['search'])) {
                $data[$attr['search']][] = $code;
                ($label !== $code) && $data[$attr['search']][] = $label;
            }

            if (isset($attr['label-filter'])) {
                $data[$attr['label-filter']][] = $label;
            }

            if (isset($attr['label-suggest'])) {
                $data[$attr['label-suggest']][] = $label;
            }
        }

        // Ok
        return $data;
    }
}
