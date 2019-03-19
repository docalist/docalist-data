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

use Docalist\Type\TypedDecimal;

/**
 * Champ standard "figure" : chiffres clés.
 *
 * Ce champ permet de saisir des chiffres clés (taille, surface, nombre de publications, effectifs,
 * chiffre d'affaires, implantations...)
 *
 * Chaque numéro comporte deux sous-champs :
 * - `type` : type de chiffre clé,
 * - `value` : valeur.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de chiffres-clés disponibles
 * ("table:figure-type" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FigureField extends TypedDecimal
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'figure',
            'label' => __('Chiffres clés', 'docalist-data'),
            'description' => __("Chiffres clés et données factuelles.", 'docalist-data'),
            'repeatable' => true,
            'fields' => [
                'type' => [
                    'table' => 'table:figure-type',
                ],
            ],
        ];
    }
}
