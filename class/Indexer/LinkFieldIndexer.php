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
use Docalist\Data\Type\Collection\IndexableMultiFieldCollection;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Search\Mapping\Field\Parameter\IndexOptions;
use Docalist\Data\Field\LinkField;

/**
 * Indexeur pour le champ "link".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LinkFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var IndexableMultiFieldCollection
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param IndexableMultiFieldCollection $field
     */
    public function __construct(IndexableMultiFieldCollection $field)
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
                    'Recherche sur les mots qui figurent dans le libellé et l\'url des liens qui figurent
                    dans le champ "link" des références docalist (quel que soit le type de lien).',
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
                    'Contient à la fois le libellé et l\'url des liens',
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
                ->url($attr['search'])
                ->setFeatures(Mapping::FULLTEXT)
                ->setLabel($this->getAttributeLabel('search'))
                ->setDescription($this->getAttributeDescription('search'));
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

        // Indexe toute les entrées
        $data = [];
        foreach ($this->field as $link) { /** @var LinkField $link */
            if (isset($link->label) && !empty($label = $link->label->getPhpValue())) {
                $data[$attr['search']][] = $label;
            }
            if (isset($link->url) && !empty($url = $link->url->getPhpValue())) {
                $data[$attr['search']][] = $url;
            }
        }

        // Ok
        return $data;
    }
}
