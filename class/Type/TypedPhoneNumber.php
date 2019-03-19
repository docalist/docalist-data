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

namespace Docalist\Data\Type;

use Docalist\Type\TypedText;
use Docalist\Data\Type\PhoneNumber;

/**
 * TypedPhoneNumber : un TypedValue qui a une valeur de type PhoneNumber.
 *
 * @property PhoneNumber $value Numéro de téléphone associé.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypedPhoneNumber extends TypedText
{
    /*
     * On hérite de TypedText et non pas de TypedNumber car :
     * - un numéro de téléphone n'est pas un nombre (on ne peut pas faire de validate_float dessus)
     * - la table d'autorité associée ne contient pas de colonne format
     */

    public static function loadSchema(): array
    {
        return [
            'label' => __('Téléphone', 'docalist-data'),
            'description' => __('Numéros de téléphone.', 'docalist-data'),
            'fields' => [
                'value' => [
                    'type' => PhoneNumber::class,
                ],
            ],
        ];
    }
}
