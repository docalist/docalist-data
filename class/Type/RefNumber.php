<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Type;

use Docalist\Type\Integer;

/**
 * Le numéro de référence de la notice.
 */
class RefNumber extends Integer
{
    public static function loadSchema()
    {
        return [
            'label' => __('Numéro de fiche', 'docalist-databases'),
            'description' => __(
                'Numéro unique attribué par docalist pour identifier la fiche au sein de la collection.',
                'docalist-databases'
            ),
        ];
    }
}
