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

namespace Docalist\Data\Field;

use Docalist\Type\TypedText;
use Docalist\Type\Url;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Data\Indexer\SourceFieldIndexer;

/**
 * Champ standard "source" : informations sur la provenance des données de l'enregistrement.
 *
 * @property Url $url Url de provenance.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SourceField extends TypedText implements Indexable
{
    /**
     * {@inheritdoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'source',
            'repeatable' => true,
            'label' => __('Source', 'docalist-data'),
            'description' => __('Informations sur la provenance des informations.', 'docalist-data'),
            'fields' => [
                'type' => [
                    'label' => __('Source', 'docalist-data'),
                    'description' => __('Code de provenance.', 'docalist-data'),
                    'table' => 'table:source-type',
                ],
                'url' => [
                    'type' => Url::class,
                    'label' => __('Url', 'docalist-data'),
                    'description' => __('Url de provenance.', 'docalist-data'),
                ],
                'value' => [
                    'label' => __('Précisions', 'docalist-data'),
                    'description' => __('Note, remarque...', 'docalist-data'),
                ],
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableTypedValueCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return SourceFieldIndexer::class;
    }

    /**
     * {@inheritdoc}
     */
    public function filterEmpty(bool $strict = true): bool
    {
        // TypedText considère qu'on est vide si on n'a que le type
        // Dans notre cas, il faut juste que l'un des champs soit rempli
        return $this->filterEmptyProperty('type', $strict)
            && $this->filterEmptyProperty('url', $strict)
            && $this->filterEmptyProperty('value', $strict);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFormat(): string
    {
        return 'link-type';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableFormats(): array
    {
        return [
            'type'              => 'Source',
            'type-value'        => 'Source (précisions)',
            'link-type'         => 'Lien sur "source"',
            'link-type-value'   => 'Lien sur "source (précisions)"',
            'link-type-tooltip' => 'Lien sur "source" avec bulle d\'aide contenant les précisions',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        switch ($format) {
            case 'type':
                return $this->span($this->formatType($options));

            case 'type-value':
                return $this->span($this->formatTypeValue($options));

            case 'link-type':
                return $this->link($this->formatType($options));

            case 'link-type-value':
                return $this->link($this->formatTypeValue($options));

            case 'link-type-tooltip':
                // si on n'a pas de lien, on ne peut pas générer la description en tooltip, on la met en libellé
                $url = $this->url->getPhpValue();
                $label = empty($url) ? $this->formatTypeValue($options) : $this->formatType($options);
                return $this->link($label, true);
        }

        return parent::getFormattedValue($options);
    }

    /**
     * Format 'type'.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatType($options = null): string
    {
        if (isset($this->type) && !empty($type = $this->formatField('type', $options))) {
            return $type;
        }

        if (isset($this->value) && !empty($value = $this->formatField('value', $options))) {
            return $value;
        }

        return $this->friendlyUrl();
    }

    /**
     * Format 'type-value'.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatTypeValue($options = null): string
    {
        $value = isset($this->value) ? $this->formatField('value', $options) : '';
        if (empty($value)) {
            return $this->formatType($options);
        }

        $type = isset($this->type) ? $this->formatField('type', $options) : '';
        if (empty($type)) {
            return $value;
        }

        return trim(sprintf('%s (%s)', $type, $value));
    }

    /**
     * Crée un span sur le texte passé en paramètre en ajoutant le code du type comme classe css.
     *
     * @param string $text Texte du lien
     *
     * @return string
     */
    private function span(string $text): string
    {
        $type = isset($this->type) ? $this->type->getPhpValue() : '';
        if (empty($type)) {
            return sprintf('<span>%s</span>', $text);
        }

        return sprintf('<span class="%s">%s</span>', esc_attr($type), $text);
    }

    /**
     * Crée un lien sur le texte passé en paramètre si on a une url dans le champ source.
     *
     * @param string    $text       Texte du lien
     * @param bool      $tooltip    Ajouter ou non le champ value en attribut title du lien généré.
     *
     * @return string
     */
    private function link(string $text, bool $tooltip = false): string
    {
        $url = $this->url->getPhpValue();
        if (empty($url)) {
            return $this->span($text);
        }
        if (empty($text)) {
            $text = $url;
        }

        $attributes = '';

        $type = isset($this->type) ? $this->type->getPhpValue() : '';
        if (isset($this->type) && !empty($type = $this->type->getPhpValue())) {
            $attributes .= sprintf(' class="%s"', esc_attr($type));
        }

        $attributes .= sprintf(' href="%s"', $url);

        $title = ($tooltip && isset($this->value)) ? $this->value->getPhpValue() : '';
        if ($tooltip && isset($this->value) && !empty($title = $this->value->getPhpValue())) {
            if ($title !== $text) {
                $attributes .= sprintf(' title="%s"', esc_attr($title));
            }
        }

        return sprintf('<a%s>%s</a>', $attributes, $text);
    }

    /**
     * Retourne une version courte de l'url pour affichage.
     *
     * @return string Le nom de domaine présent dans l'url, sans le protocole éventuel
     *
     * Exemple : https://www.google.fr/images/zz -> google.fr
     */
    private function friendlyUrl(): string
    {
        if (!isset($this->url) || empty($url = $this->url->getPhpValue())) {
            return '';
        }

        $pt = strpos($url, '://');
        if ($pt !== false) {
            $url = substr($url, $pt + 3);
        }

        if ('www.' === strtolower(substr($url, 0, 4))) {
            $url = substr($url, 4);
        }

        $pt = strpos($url, '/');
        if ($pt !== false) {
            $url = substr($url, 0, $pt);
        }

        return $url;
    }
}
