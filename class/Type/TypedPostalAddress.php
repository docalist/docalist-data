<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Type;

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Data\Type\PostalAddress;

/**
 * Adresse postale typée : un type composite associant un type provenant d'une table d'autorité à une valeur
 * de type PostalAddress.
 *
 * @property TableEntry     $type   Type d'adresse (principale, secondaire...)
 * @property PostalAddress  $value  Adresse postale associée.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypedPostalAddress extends TypedText
{
    public static function loadSchema()
    {
        return [
            'label' => __('Adresse', 'docalist-data'),
            'description' => __("Adresse et type d'adresse.", 'docalist-data'),
            'editor' => 'container',
            'fields' => [
                'type' => [
                    'table' => 'table:postal-address-type',
                ],
                'value' => [
                    'type' => 'Docalist\Data\Type\PostalAddress',
                    'label' => __('Adresse', 'docalist-data'),
                ],
            ],
            'default' => [['type' => 'main']],
        ];
    }
}
