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

namespace Docalist\Data\Entity;

use Docalist\Data\Record;
use Docalist\Data\Field\ContentField;
use Docalist\Data\Field\TopicField;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Data\Type\Collection\IndexableTopicCollection;
use Docalist\Data\GridBuilder\EditGridBuilder;

/**
 * Un contenu simple dans une base docalist.
 *
 * Le type docalist "content" est un type de base très versatile qui permet de créer facilement de nouveaux types de
 * contenus dans WordPress (une FAQ, un centre d'aide, un portfolio, des témoignages...)
 *
 * Chaque enregistrement dispose d'un titre, d'un champ content (multivalué) et d'un champ topic (multivalué).
 *
 * @property IndexableTypedValueCollection  $content    Contenus.
 * @property IndexableTopicCollection       $topic      Mots-clés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ContentEntity extends Record
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'content',
            'label' => __('Contenu de base', 'docalist-data'),
            'description' => __('Un contenu de base (titre, texte et mots-clés).', 'docalist-data'),
            'fields' => [
                'posttitle' => [], // Hérité de Record, listé pour avoir le bon ordre dans la grille de base
                'content'   => ContentField::class,
                'topic'     => TopicField::class,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getEditGrid()
    {
        $builder = new EditGridBuilder(self::class);

        // $builder->setProperty('stylesheet', 'docalist-people-edit-organization');

        $builder->addGroup(
            __('Contenu de base', 'docalist-people'),
            'posttitle,content,topic'
        );

        $builder->addGroup(
            __('Informations de gestion', 'docalist-people'),
            'type,ref,source',
            'collapsed'
        );

        return $builder->getGrid();
    }
}
