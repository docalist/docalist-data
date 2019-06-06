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
use Docalist\Data\Field\RefField;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;

/**
 * Indexeur pour le champ "ref".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RefFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var RefField
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param RefField $field
     */
    public function __construct(RefField $field)
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
                return __('Recherche par numéro de référence docalist.', 'docalist-data');
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
                    'Utilisable comme filtre et comme clé de tri.',
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

        // Champ de recherche
        $form->checkbox()
            ->setName('search')
            ->setAttribute('disabled') // toujours généré, non désactivable
            ->setLabel($this->getAttributeName('search'))
            ->setDescription($this->getAttributeLabel('search') . ' ' . __(
                'Cet attribut est toujours généré.',
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

        // toujours activé
        $mapping
            ->long($this->getAttributeName('search'))
            ->setFeatures(Mapping::FILTER | Mapping::EXCLUSIVE | Mapping::SORT)
            ->setLabel($this->getAttributeLabel('search'))
            ->setDescription($this->getAttributeDescription('search'));

        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData(): array
    {
        $data = [];

        $value = $this->field->getPhpValue();
        !empty($value) && $data[$this->getAttributeName('search')] = $value;

        return $data;
    }
}
