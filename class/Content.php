<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data;

use Docalist\Data\Field\TitleField;
use Docalist\Data\Field\ContentField;
use Docalist\Data\Field\TopicField;

/**
 * Un contenu simple dans une base docalist.
 *
 * Le type docalist "content" est un type de base très versatile qui permet de créer facilement de nouveaux types de
 * contenus dans WordPress (une FAQ, un centre d'aide, un portfolio, des témoignages...)
 *
 * Chaque enregistrement dispose d'un titre, d'un champ content (multivalué) et d'un champ topic (multivalué).
 *
 * @property TitleField     $title      Titre.
 * @property ContentField[] $content    Contenus.
 * @property TopicField[]   $topic      Mots-clés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Content extends Record
{
    public static function loadSchema()
    {
        return [
            'name' => 'content',
            'label' => __('Contenu de base', 'docalist-data'),
            'description' => __('Un contenu de base (titre, texte et mots-clés).', 'docalist-data'),
            'fields' => [
                'posttitle' => 'Docalist\Data\Field\PostTitleField',
                'content'   => 'Docalist\Data\Field\ContentField*',
                'topic'     => 'Docalist\Data\Field\TopicField*',
            ],
        ];
    }
}
