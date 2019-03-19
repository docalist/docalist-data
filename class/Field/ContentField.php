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
class ContentField extends TypedLargeText
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'content',
            'label' => __('Contenu', 'docalist-data'),
            'description' => __('Contenus textuels.', 'docalist-data'),
            'repeatable' => true,
            'fields' => [
                'type' => [
                    'table' => 'table:content-type',
                ],
                'value' => [
                    'editor' => 'wpeditor-teeny',
                ]
            ],
            'editor' => 'integrated',
        ];
    }
}
