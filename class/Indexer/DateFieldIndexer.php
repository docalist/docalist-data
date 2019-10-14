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
use Docalist\Data\Field\DateField;
use Docalist\Search\Mapping\Field\Parameter\IndexOptions;

/**
 * Indexeur pour le champ "date".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DateFieldIndexer extends FieldIndexer
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
                        'Recherche sur les dates qui figurent dans le champ "date" des références
                        docalist (quel que soit le type de date).',
                        'docalist-data'
                    )
                    : sprintf(
                        __(
                            'Recherche sur les dates de type "%s" qui figurent dans le champ "date"
                            des références docalist.',
                            'docalist-data'
                        ),
                        $type
                    );

            case 'filter':
                return
                    empty($type)
                    ? __(
                        'Filtre, agrégation et tri sur la première des dates qui figure dans le champ "date"
                        des références docalist, quel que soit son type.',
                        'docalist-data'
                    )
                    : sprintf(
                    __(
                        'Filtre, agrégation et tri sur les dates de type "%s" qui figurent dans le champ "date"
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
                        'Contient tous les types de date (date de début, date de publication...)',
                        'docalist-data'
                    )
                    : sprintf(
                        __('Contient uniquement les dates de type "%s".', 'docalist-data'),
                        $type
                    );

                $description .= ' ' . __(
                    'Chaque date est indexée sous trois formes : yyyy (année uniquement), yyyymm (année et mois)
                    et yyyymmdd (année, mois et jour).',
                    'docalist-data'
                );

                return $description;

            case 'filter':
                return __(
                    'La date est stockée sous la forme d\'un nombre (secondes écoulées depuis epoch)
                    qui peut être utilisé à la fois comme filtre, comme agrégation et comme clé de tri.',
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
        $item = $this->field->createTemporaryItem(); /** @var DateField $item */

        // Récupère la liste des types de date disponibles
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
                'Vous pouvez créer des champs de recherche spécifiques (qui portent sur un seul type de date)
                en sélectionnant des types dans la liste ci-dessus (le nom du champ de recherche qui
                sera créé est indiqué entre parenthèses).',
                'docalist-data'
            ));

        // Filtre, agrégation et tri sur la première date
        $form->checkbox()
            ->setName('filter')
            ->setLabel($this->getAttributeName('filter'))
            ->setDescription($this->getAttributeLabel('filter'));

        // Filtres, agrégations et tri sur des dates spécifiques
        $form->add($item->type->getEditorForm())
            ->setName('filter-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('filter', $types))
            ->setLabel(__('Filtres et clés de tri', 'docalist-data'))
            ->setDescription(__(
                'Vous pouvez créer des filtres spécifiques (qui portent sur un seul type de date)
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

        // Filtres, agrégations et tri
        $fields = $attr['filter-types'] ?? [];                      // spécifiques
        isset($attr['filter']) && $fields[''] = $attr['filter'];    // générique (première date)
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
        foreach ($this->field as $item) { /** @var DateField $item */
            $date = $item->value->getPhpValue();
            $type = $item->type->getPhpValue();

            $searchString = $this->getSearchString($date);

            isset($attr['search']) && $data[$attr['search']][] = $searchString;
            isset($attr['search-types'][$type]) && $data[$attr['search-types'][$type]][] = $searchString;
            isset($attr['filter-types'][$type]) && $data[$attr['filter-types'][$type]][] = $date;

            if (isset($attr['filter']) && empty($data[$attr['filter']])) { // la première date uniquement
                $data[$attr['filter']][] = $date;
            }
        }

        // Ok
        return $data;
    }

    private function getSearchString(string $date): string
    {
        $length = strlen($date);

        if ($length < 4) {
            return '';
        }

        $year = substr($date, 0, 4); // year
        if (strlen($date) < 6) {
            return $year;
        }

        $month = substr($date, 4, 2);
        if (strlen($date) < 8) {
            return $year . ' ' . $year . $month;
        }

        $day = substr($date, 6, 2);

        return $year . ' ' . $year . $month . ' ' . $year . $month . $day;
    }
}
