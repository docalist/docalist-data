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
use Docalist\Data\Type\Collection\IndexableCollection;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Type\TableEntry;

/**
 * Indexeur standard pour les champs de type TableEntry.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TableEntryIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var IndexableCollection
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param IndexableCollection $field
     */
    public function __construct(IndexableCollection $field)
    {
        parent::__construct($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeLabel(string $attribute, string $type = ''): string
    {
        $field = $this->getFieldName();
        switch ($attribute) {
            case 'search':
                return sprintf(
                    __('Recherche sur le champ "%s" des références docalist.', 'docalist-data'),
                    $field
                );

            case 'label-filter':
                return sprintf(
                    __('Filtre sur le champ "%s" des références docalist.', 'docalist-data'),
                    $field
                );

            case 'label-suggest':
                return sprintf(
                    __('Autocomplete sur les entrées du champ "%s" des références docalist.', 'docalist-data'),
                    $field
                );
        }

        return parent::getAttributeLabel($attribute, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeDescription(string $attribute, string $type = ''): string
    {
        $field = $this->getFieldName();
        switch ($attribute) {
            case 'search':
                return sprintf(
                    __(
                        'Pour chacune des entrées du champ "%s", contient à la fois le code et le libellé
                        qui figure dans la table d\'autorité associée au champ.',
                        'docalist-data'
                    ),
                    $field
                );

            case 'label-filter':
                return sprintf(
                    __(
                        'Cet attribut est un filtre sur le libellé des entrées qui figurent dans le champ "%s"
                        des références docalist. Les libellés indexés sont ceux qui figurent dans la table
                        d\'autorité associée au champ.',
                        'docalist-data'
                    ),
                    $field
                );

            case 'label-suggest':
                return sprintf(
                    __(
                        'Contient le libellé des entrées qui figurent dans la table d\'autorité ou
                         le thésaurus associé au champ "%s" des références docalist.',
                        'docalist-data'
                    ),
                    $field
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
                ->text($attr['search'])
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
                ->setLabel($this->getAttributeLabel('suggest'))
                ->setDescription($this->getAttributeDescription('suggest'));
        }

        // Ok
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

        // Indexe toute les entrées
        $data = [];
        foreach ($this->field as $item) { /** @var TableEntry $item */
            $code = $item->getPhpValue();
            $label = $item->getEntryLabel();

            if (isset($attr['search'])) {
                $data[$attr['search']][] = $code;
                $data[$attr['search']][] = $label;
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
