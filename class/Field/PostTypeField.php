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

use Docalist\Type\ListEntry;
use Docalist\Data\Database;

/**
 * Champ standard "posttype" : nom de code de la base docalist où est stocké l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_type".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostTypeField extends ListEntry
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'posttype',
            'label' => __('Base Docalist', 'docalist-data'),
            'description' => __('Base Docalist où est enregistrée la fiche (type de post).', 'docalist-data'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $value = $this->getPhpValue();
        $database = docalist('docalist-data')->database($value); /** @var Database $database */

        return $database ? $database->getLabel() : $value;
    }

    /**
     * Retourne la liste des bases Docalist.
     *
     * @return array Un tableau de la forme [post-type => Libellé de la base].
     */
    protected function getEntries(): array
    {
        $result = [];
        foreach (docalist('docalist-data')->databases() as $postType => $database) { /** @var Database $database */
            $result[$postType] = $database->getLabel();
        }

        asort($result, SORT_NATURAL);

        return $result;
    }
}
