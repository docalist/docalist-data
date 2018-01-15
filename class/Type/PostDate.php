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

use Docalist\Type\DateTime;

/**
 * La date de création de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostDate extends DateTime
{
    public static function loadSchema()
    {
        return [
            'label' => __('Création', 'docalist-data'),
            'description' => __('Date/heure de création de la fiche.', 'docalist-data'),
        ];
    }
}
