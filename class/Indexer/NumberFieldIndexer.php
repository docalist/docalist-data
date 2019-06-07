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
use Docalist\Data\Field\NumberField;

/**
 * Indexeur pour le champ "number".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class NumberFieldIndexer extends FieldIndexer
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
                return
                    empty($type)
                    ? __(
                        'Recherche sur les numéros qui figurent dans le champ "number" des références
                        docalist (quel que soit le type de numéro).',
                        'docalist-data'
                    )
                    : sprintf(
                        __(
                            'Recherche sur les numéros de type "%s" qui figurent dans le champ "number"
                            des références docalist.',
                            'docalist-data'
                        ),
                        $type
                    );

            case 'filter':
                return sprintf(
                    __(
                        'Filtre sur les numéros de type "%s" qui figurent dans le champ "number"
                        des références docalist.',
                        'docalist-data'
                    ),
                    $type
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
                $description =
                    empty($type)
                    ? __(
                        'Contient tous les types de numéros (SIRET, ISBN...)',
                        'docalist-data'
                    )
                    : sprintf(
                        __('Contient uniquement les numéros de type "%s".', 'docalist-data'),
                        $type
                    );

                $description .= ' ' . __(
                    'Chaque numéro est indexé deux fois : une fois tel qu\'il a été saisi (par exemple
                    "abc-12-yz") et une fois sous une forme condensée ne contenant que les lettres
                    et les chiffres (par exemple "abc12yz").',
                    'docalist-data'
                );

                return $description;

            case 'filter':
                return __(
                    'Contient les numéros tels qu\'ils figurent dans les références docalist
                    (avec les tirets et signes de ponctuation éventuels).',
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

        // Crée un item temporaire
        $item = $this->field->createTemporaryItem(); /** @var NumberField $item */

        // Récupère la liste des types de numéros disponibles
        $types = $item->type->getEntries();

        // Champ de recherche
        $form->checkbox()
            ->setName('search')
            ->setLabel($this->getAttributeName('search'))
            ->setDescription($this->getAttributeLabel('search'));

        // Champs de recherche spécifiques
        $form->add($item->type->getEditorForm())
            ->setName('search-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('search', $types))
            ->setLabel(__('Champs de recherche spécifiques', 'docalist-data'))
            ->setDescription(__(
                'Vous pouvez créer des champs de recherche spécifiques (qui portent sur un seul type de numéro)
                en sélectionnant des types dans la liste ci-dessus (le nom du champ de recherche qui
                sera créé est indiqué entre parenthèses).',
                'docalist-data'
            ));

        // Filtres/facettes spécifiques
        $form->add($item->type->getEditorForm())
            ->setName('filter-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('filter', $types))
            ->setLabel(__('Filtres et clés de tri', 'docalist-data'))
            ->setDescription(__(
                'Vous pouvez créer des filtres spécifiques (qui portent sur un seul type de numéro)
                en sélectionnant des types dans la liste ci-dessus (le nom du filtre qui
                sera créé est indiqué entre parenthèses). Les filtres générés sont également utilisables
                comme clés de tri.',
                'docalist-data'
            ));

        return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(): Mapping
    {
        $mapping = parent::getMapping();

        $attr = $this->getAttributes();

        // Champs de recherche
        $fields = $attr['search-types'] ?? [];                      // spécifiques
        isset($attr['search']) && $fields[''] = $attr['search'];    // générique
        foreach ($fields as $type => $name) {
            $mapping
                ->literal($name)
                ->setIndexOptions(IndexOptions::INDEX_DOCS)
                ->setFeatures(Mapping::FULLTEXT)
                ->setLabel($this->getAttributeLabel('search', $type))
                ->setDescription($this->getAttributeDescription('search', $type));
        }

        // Filtres
        $fields = $attr['filter-types'] ?? [];                      // spécifiques
        foreach ($fields as $type => $name) {
            $mapping
                ->date($name)
                ->setFeatures(Mapping::AGGREGATE | Mapping::FILTER | Mapping::SORT)
                ->setLabel($this->getAttributeLabel('filter', $type))
                ->setDescription($this->getAttributeDescription('filter', $type));
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

        // Indexe toutes les entrées
        $data = [];
        foreach ($this->field as $item) { /** @var NumberField $item */
            $number = $item->value->getPhpValue();
            $type = $item->type->getPhpValue();

            $searchString = $this->getSearchString($number);

            isset($attr['search']) && $data[$attr['search']][] = $searchString;
            isset($attr['search-types'][$type]) && $data[$attr['search-types'][$type]][] = $searchString;
            isset($attr['filter-types'][$type]) && $data[$attr['filter-types'][$type]][] = $number;
        }

        // Ok
        return $data;
    }

    private function getSearchString(string $number): string
    {
        return $number . ' ' . str_replace(' ', '', $this->getSortKey($number));
    }
}
