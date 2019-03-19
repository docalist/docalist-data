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

use Docalist\Type\DateTime;

/**
 * Champ standard "lastupdate" : date/heure de dernière modification de l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_modified".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostModifiedField extends DateTime
{
    public static function loadSchema()
    {
        return [
            'name' => 'lastupdate',
            'label' => __('Dernière modification', 'docalist-data'),
            'description' => __('Date/heure de dernière modification de la fiche.', 'docalist-data'),
        ];
    }
}
