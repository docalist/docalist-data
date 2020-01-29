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

use Docalist\Data\Type\Topic;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableTopicCollection;
use Docalist\Data\Indexer\TopicFieldIndexer;
use Docalist\Forms\Container;

/**
 * Champ standard "topic" : Une liste de mots-clés d'un certain type.
 *
 * Ce champ permet de saisir des tags et des mots-clés pour une entité.
 *
 * Chaque occurence comporte deux sous-champs :
 * - `type` : type d'indexation,
 * - `value` : listes des mots-clés pour ce type d'indexation.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les différents types d'indexation disponibles
 * ("table:topic-type" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TopicField extends Topic implements Indexable
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'topic',
            'repeatable' => true,
            'fields' => [
                'type' => [
                    'table' => 'table:topic-type',
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableTopicCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return TopicFieldIndexer::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatSettingsForm(): Container
    {
        $form = parent::getFormatSettingsForm();

        $form->checkbox('searchlink')
            ->setLabel(__('Liens rebonds', 'docalist-data'))
            ->setDescription(__(
                "Génère un lien de recherche pour chaque mot-clé
                (l'attribut <code>filter.topic.label</code> doit être actif).",
                'docalist-data'
            ));

        return $form;
    }


    /**
     * {@inheritDoc}
     */
    final protected function getFormattedTerms($options): array
    {
        $terms = $this->getTermsLabel();

        if ($this->getOption('searchlink', $options)) {
            $baseUrl = apply_filters('docalist_search_get_search_page_url', '');
            if (!empty($baseUrl)) {
                foreach ($terms as $code => $label) {
                    $url = $baseUrl . '?filter.topic.label=' . urlencode($label);
                    $title = __("Rechercher ce mot-clé", 'docalist-data');
                    $terms[$code] = sprintf(
                        '<a class="searchlink" href="%s" title="%s">%s</a>',
                        esc_attr($url),
                        esc_attr($title),
                        $label
                    );
                }
            }
        }

        return $terms;
    }
}
