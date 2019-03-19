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
class TopicField extends Topic
{
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
}
