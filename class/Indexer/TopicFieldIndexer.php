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
use Docalist\Data\Type\Collection\TopicCollection;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Search\Mapping\Field\Parameter\IndexOptions;
use Docalist\Data\Field\TopicField;

/**
 * Indexeur pour le champ "topic".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TopicFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var TopicCollection
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param TopicCollection $field
     */
    public function __construct(TopicCollection $field)
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
                        'Recherche sur les mots-clés qui figurent dans le champ "topic" des références
                        docalist (quel que soit le type de topic).',
                        'docalist-data'
                    )
                    : sprintf(
                        __(
                            'Recherche sur les mots-clés de type "%s" qui figurent dans le champ "topic"
                            des références docalist.',
                            'docalist-data'
                        ),
                        $type
                    );

            case 'label-filter':
                return
                    empty($type)
                    ? __(
                        'Filtre sur les mots-clés qui figurent dans le champ "topic" des références
                        docalist (quel que soit le type de topic).',
                        'docalist-data'
                    )
                    : sprintf(
                        __(
                            'Filtre sur les mots-clés de type "%s" qui figurent dans le champ "topic"
                            des références docalist.',
                            'docalist-data'
                        ),
                        $type
                    );

            case 'label-hierarchy':
                return
                    sprintf(
                        __(
                            'Filtre hiérarchique sur l\'arborescence des mots-clés de type "%s" qui
                            figurent dans le champ "topic" des références docalist.',
                            'docalist-data'
                        ),
                        $type
                    );

            case 'label-suggest':
                return
                    empty($type)
                    ? __(
                        'Autocomplete sur les mots-clés qui figurent dans le champ "topic" des
                        références docalist (quel que soit le type de topic).',
                        'docalist-data'
                    )
                    : sprintf(
                        __(
                            'Autocomplete sur les mots-clés de type "%s" qui figurent dans le
                            champ "topic" des références docalist.',
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
                        'Contient tous les types de topics (descripteurs, tatouage, mots-clés libres...)',
                        'docalist-data'
                    )
                    : sprintf(
                        __('Contient uniquement les topics de type "%s".', 'docalist-data'),
                        $type
                    );

                $description .= ' ' . __(
                    'Chaque mot-clé est indexé en utilisant le libellé qui figure dans la table
                    d\'autorité ou le thésaurus associé au champ. Pour les mots-clés libres, c\'est la
                    valeur saisie qui est indexée. Supporte la recherche par mot et par expression.',
                    'docalist-data'
                );

                return $description;

            case 'label-filter':
                $description =
                    empty($type)
                    ? __(
                        'Contient tous les types de topics (descripteurs, tatouage, mots-clés libres...)',
                        'docalist-data'
                    )
                    : sprintf(
                        __('Contient uniquement les topics de type "%s".', 'docalist-data'),
                        $type
                    );

                $description .= ' ' . __(
                    'Le filtre contient le libellé du mot-clé qui figure dans la table d\'autorité ou
                    le thésaurus associé au champ. Pour les mots-clés libres, c\'est la valeur saisie
                    qui est utilisée comme filtre.',
                    'docalist-data'
                );

                return $description;

            case 'label-hierarchy':
                return sprintf(
                    __(
                        'Utilise le thésaurus associé aux topics de type "%s" pour contruire un path de
                        la forme "niveau1/niveau2/mot-clé" qui fournit l\'arborescence compléte de chacun
                        des descripteurs qui figurent dans la référence docalist.
                        Permet de créer une facette hiérarchique (par niveau) sur ce type de topic.',
                        'docalist-data'
                    ),
                    $type
                );

            case 'label-suggest':
                $description =
                    empty($type)
                    ? __(
                        'Contient tous les types de topics (descripteurs, tatouage, mots-clés libres...)',
                        'docalist-data'
                    )
                    : sprintf(
                        __('Contient uniquement les topics de type "%s".', 'docalist-data'),
                        $type
                    );

                $description .= ' ' . __(
                    'Contient le libellé du mot-clé qui figure dans la table d\'autorité ou
                    le thésaurus associé au champ. Pour les mots-clés libres, c\'est la valeur saisie
                    qui est utilisée comme filtre.',
                    'docalist-data'
                );

                return $description;
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
        $item = $this->field->createTemporaryItem(); /** @var TopicField $item */

        // Récupère la liste des types de topics disponibles
        $types = $item->type->getEntries();

        // Champ de recherche générique
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
                'Vous pouvez créer des champs de recherche spécifiques (qui portent sur un seul type de topic)
                en sélectionnant des types dans la liste ci-dessus (le nom du champ de recherche qui
                sera créé est indiqué entre parenthèses).',
                'docalist-data'
            ));

        // Filtre générique
        $form->checkbox()
            ->setName('label-filter')
            ->setLabel($this->getAttributeName('label-filter'))
            ->setDescription($this->getAttributeLabel('label-filter'));

        // Filtres spécifiques
        $form->add($item->type->getEditorForm())
            ->setName('label-filter-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('label-filter', $types))
            ->setLabel(__('Filtres spécifiques', 'docalist-data'))
            ->setDescription(__(
                'Vous pouvez créer des filtres spécifiques (qui portent sur un seul type de topic)
                en sélectionnant des types dans la liste ci-dessus (le nom du filtre qui
                sera créé est indiqué entre parenthèses).',
                'docalist-data'
            ));

        // Filtres hiérarchiques
        $form->add($item->type->getEditorForm())
            ->setName('label-hierarchy-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('label-hierarchy', $item->getThesaurusTopics()))
            ->setLabel(__('Filtres hiérarchiques', 'docalist-data'))
            ->setDescription(__(
                'Pour les topics de type "thesaurus", vous pouvez créer des filtres supplémentaires
                qui permettent de faire des facettes hiérarchiques (le nom du filtre qui sera créé est
                indiqué entre parenthèses).',
                'docalist-data'
            ));

        // Autocomplete générique
        $form->checkbox()
            ->setName('label-suggest')
            ->setLabel($this->getAttributeName('label-suggest'))
            ->setDescription($this->getAttributeLabel('label-suggest'));

        // Autocompletes spécifiques
        $form->add($item->type->getEditorForm())
            ->setName('label-suggest-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('label-suggest', $types))
            ->setLabel(__('Autocompletes spécifiques', 'docalist-data'))
            ->setDescription(__(
                'Vous pouvez créer des autocompletes spécifiques (qui portent sur un seul type de topic)
                en sélectionnant des types dans la liste ci-dessus (le nom de l\autocomplte qui
                sera créé est indiqué entre parenthèses).',
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
        $fields = $attr['search-types'] ?? [];                                  // spécifiques
        isset($attr['search']) && $fields[''] = $attr['search'];                // générique
        foreach ($fields as $type => $name) {
            $mapping
                ->text($name)
                ->setFeatures(Mapping::FULLTEXT)
                ->setLabel($this->getAttributeLabel('search', $type))
                ->setDescription($this->getAttributeDescription('search', $type));
        }

        // Filtres
        $fields = $attr['label-filter-types'] ?? [];                            // spécifiques
        isset($attr['label-filter']) && $fields[''] = $attr['label-filter'];    // générique
        foreach ($fields as $type => $name) {
            $mapping
                ->keyword($name)
                ->setFeatures(Mapping::AGGREGATE | Mapping::FILTER)
                ->setLabel($this->getAttributeLabel('label-filter', $type))
                ->setDescription($this->getAttributeDescription('label-filter', $type));
        }

        // Filtres hiérarchiques
        $fields = $attr['label-hierarchy-types'] ?? [];                         // spécifiques
        foreach ($fields as $type => $name) {
            $mapping
                ->hierarchy($name)
                ->setFeatures(Mapping::AGGREGATE | Mapping::FILTER)
                ->setLabel($this->getAttributeLabel('label-hierarchy', $type))
                ->setDescription($this->getAttributeDescription('label-hierarchy', $type));
        }

        // Autocompletes
        $fields = $attr['label-suggest-types'] ?? [];                           // spécifiques
        isset($attr['label-suggest']) && $fields[''] = $attr['label-suggest'];  // générique
        foreach ($fields as $type => $name) {
            $mapping
                ->suggest($name)
                ->setFeatures(Mapping::LOOKUP)
                ->setLabel($this->getAttributeLabel('label-suggest', $type))
                ->setDescription($this->getAttributeDescription('label-suggest', $type));
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
        foreach ($this->field as $item) { /** @var TopicField $item */
            $type = $item->type->getPhpValue();
            $labels = array_values($item->getTermsLabel()); // on veut pas des clés qui contiennent le code

            if (isset($attr['search'])) {
                $this->add($data[$attr['search']], $labels);
            }

            if (isset($attr['search-types'][$type])) {
                $this->add($data[$attr['search-types'][$type]], $labels);
            }

            if (isset($attr['label-filter'])) {
                $this->add($data[$attr['label-filter']], $labels);
            }

            if (isset($attr['label-filter-types'][$type])) {
                $this->add($data[$attr['label-filter-types'][$type]], $labels);
            }

            if (isset($attr['label-suggest'])) {
                $this->add($data[$attr['label-suggest']], $labels);
            }

            if (isset($attr['label-suggest-types'][$type])) {
                $this->add($data[$attr['label-suggest-types'][$type]], $labels);
            }

            if (isset($attr['label-hierarchy-types'][$type])) {
                $this->add($data[$attr['label-hierarchy-types'][$type]], array_values($item->getTermsPath()));
            }
        }

        // Ok
        return $data;
    }

    private function add(&$dest, array $items): void
    {
        is_array($dest) ? array_push($dest, ...$items) : ($dest = $items);
    }
}
