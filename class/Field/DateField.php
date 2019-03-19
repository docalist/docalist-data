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

use Docalist\Type\TypedFuzzyDate;

/**
 * Champ standard "date" : dates.
 *
 * Ce champ permet d'indiquer des dates (date de création, date de fin...)
 *
 * Chaque date comporte deux sous-champs :
 * - `type` : type de date,
 * - `value` : date.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de dates disponibles
 * ("table:date-type" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DateField extends TypedFuzzyDate
{
    public static function loadSchema()
    {
        return [
            'name' => 'date',
            'label' => __('Dates', 'docalist-data'),
            'description' => __('Dates.', 'docalist-data'),
            'repeatable' => true,
            'fields' => [
                'type' => [
                    'table' => 'table:date-type',
                ],
                'value' => [
                    'label' => __('Date', 'docalist-data'),
                ],
            ],
        ];
    }
}
