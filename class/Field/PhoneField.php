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

use Docalist\Data\Type\TypedPhoneNumber;

/**
 * Champ standard "phone" : numéros de téléphone.
 *
 * Ce champ permet de saisir les numéros de téléphones d'une entité.
 *
 * Chaque numéro comporte deux sous-champs :
 * - `type` : type de numéro,
 * - `value` : numéro de téléphone.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de numéros disponibles
 * ("table:phone-number" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PhoneField extends TypedPhoneNumber
{
    public static function loadSchema()
    {
        return [
            'name' => 'phone',
            'repeatable' => true,
        ];
    }
}
