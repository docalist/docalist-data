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

use Docalist\Data\Field\Title;
use Docalist\Data\Field\Content as ContentField;
use Docalist\Data\Field\Topic;

/**
 * Un contenu simple dans une base docalist.
 *
 * Le type docalist "content" est un type de base très versatile qui permet de créer facilement de nouveaux types de
 * contenus dans WordPress (une FAQ, un centre d'aide, un portfolio, des témoignages...)
 *
 * Chaque enregistrement dispose d'un titre, d'un champ content (multivalué) et d'un champ topic (multivalué).
 *
 * @property Title          $title      Titre.
 * @property ContentField[] $content    Contenus.
 * @property Topic[]        $topic      Mots-clés.
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
            'description' => __('Contenu simple.', 'docalist-data'),
            'fields' => [
                'title'     => 'Docalist\Data\Field\Title',
                'content'   => 'Docalist\Data\Field\Content*',
                'topic'     => 'Docalist\Data\Field\Topic*',
            ],
        ];
    }

    /**
     * Initialise le champ post_title à partir du champ title lorsque la notice est enregistrée.
     */
    protected function initPostTitle()
    {
        isset($this->title) && $this->posttitle = $this->title->getPhpValue();
    }
}
