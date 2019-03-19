<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Field;

use Docalist\Type\Integer;

/**
 * Champ docalist standard "ref" : numéro de l'enregistrement.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RefField extends Integer
{
    public static function loadSchema()
    {
        return [
            'name' => 'ref',
            'label' => __('Numéro de fiche', 'docalist-data'),
            'description' => __(
                'Numéro unique attribué par docalist pour identifier la fiche au sein de la collection.',
                'docalist-data'
            ),
        ];
    }
}
