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
use Docalist\Search\Indexer\Field\PostContentIndexer;

/**
 * Indexeur pour le champ "content".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ContentFieldIndexer extends FieldIndexer
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
                        'Recherche sur les contenus qui figurent dans le champ "content" des références
                        docalist (quel que soit le type de contenu).',
                        'docalist-data'
                    )
                    : sprintf(
                        __(
                            'Recherche sur les contenus de type "%s" qui figurent dans le champ "content"
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
            case 'search': // quand on n'a pas de type c'est PostContentIndexer qui fournit la description
                return sprintf(
                    __('Contient uniquement les contenus de type "%s".', 'docalist-data'),
                    $type
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
        $item = $this->field->createTemporaryItem(); /** @var ContentField $item */

        // Récupère la liste des types de contenus disponibles
        $types = $item->type->getEntries();

        // Champ de recherche
        $form->checkbox()
            ->setName('search')
            ->setAttribute('disabled') // toujours généré, non désactivable
            ->setLabel($this->getAttributeName('search'))
            ->setDescription($this->getAttributeLabel('search') . ' ' . __(
                'Cet attribut est toujours généré.',
                'docalist-data'
            ));

        // Champs de recherche spécifiques
        $form->add($item->type->getEditorForm())
            ->setName('search-types')
            ->setAttribute('multiple')
            ->setOptions($this->prepareSelect('search', $types))
            ->setLabel(__('Champs de recherche spécifiques', 'docalist-data'))
            ->setDescription(__(
                'Vous pouvez créer des champs de recherche spécifiques (qui portent sur un seul type de contenu)
                en sélectionnant des types dans la liste ci-dessus (le nom du champ de recherche qui
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

        // Champ de recherche générique (toujours généré)
        PostContentIndexer::buildMapping($mapping);

        // Champs de recherche spécifiques
        $fields = $attr['search-types'] ?? [];
        foreach ($fields as $type => $name) {
            $mapping
                ->text($name)
                ->copyTo(PostContentIndexer::SEARCH_FIELD)
                ->setFeatures(Mapping::FULLTEXT)
                ->setLabel($this->getAttributeLabel('search', $type))
                ->setDescription($this->getAttributeDescription('search', $type));
        }

        // Ok
        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData(): array
    {
        // Si la collection est vide, terminé (le champ est toujours indexé)
        if (0 === $this->field->count()) {
            return [];
        }

        // Récupère la liste des attributs à générer
        $attr = $this->getAttributes();

        // Indexe toutes les entrées
        $data = [];
        foreach ($this->field as $item) { /** @var ContentField $item */
            $type = $item->type->getPhpValue();
            $content = strip_tags($item->value->getPhpValue());

            // On stocke dans content-<type> (qui fait un copy_to) si c'est un champ distinct, dans content sinon
            $field =
                isset($attr['search-types'][$type])
                ? $attr['search-types'][$type]
                : PostContentIndexer::SEARCH_FIELD;

            $data[$field] = $content;
        }

        // Ok
        return $data;
    }
}
