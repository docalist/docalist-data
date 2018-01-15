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

use Docalist\Type\Integer;

/**
 * Le numéro de référence de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RefNumber extends Integer
{
    public static function loadSchema()
    {
        return [
            'label' => __('Numéro de fiche', 'docalist-data'),
            'description' => __(
                'Numéro unique attribué par docalist pour identifier la fiche au sein de la collection.',
                'docalist-data'
            ),
        ];
    }
}
