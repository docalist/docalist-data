<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\TypedLargeText;

/**
 * Champ standard "content" : contenu de l'enregistrement.
 *
 * Ce champ permet de saisir des contenus textuels (présentation, description, résumé...)
 *
 * Chaque occurence comporte deux sous-champs :
 * - `type` : type de contenu,
 * - `value` : contenu.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de contenus disponibles
 * ("table:content-type" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Content extends TypedLargeText
{
    public static function loadSchema()
    {
        return [
            'label' => __('Contenu', 'docalist-data'),
            'description' => __('Contenus textuels.', 'docalist-data'),
            'fields' => [
                'type' => [
                    'table' => 'table:content-type',
                ],
                'value' => [
                    'editor' => 'wpeditor-teeny',
                ]
            ],
            'default' => [['type' => 'content']],
            'editor' => 'integrated',
        ];
    }
}
