<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Type;

use Docalist\Type\TypedValue;
use Docalist\Data\Type\PostalAddress;

/**
 * TypedPostalAddress : un TypedValue qui a une valeur de type PostalAddress.
 *
 * @property PostalAddress $value Adresse postale associée.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypedPostalAddress extends TypedValue
{
    public static function loadSchema()
    {
        return [
            'label' => __('Adresse postale', 'docalist-data'),
            'description' => __("Adresse et type d'adresse.", 'docalist-data'),
            'editor' => 'container',
            'fields' => [
                'type' => [
                    'description' => __("Précision sur le type d'adresse.", 'docalist-data'),
                ],
                'value' => [
                    'type' => PostalAddress::class,
                    'label' => __('Adresse', 'docalist-data'),
                ],
            ],
        ];
    }
}
