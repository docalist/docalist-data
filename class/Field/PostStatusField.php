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

/**
 * Champ standard "status" : statut de visibilité de l'enregistrement.
 *
 * Ce champ Docalist correspond au champ WordPress "post_status".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostStatusField extends ListEntry
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'status',
            'label' => __('Statut WordPress', 'docalist-data'),
            'description' => __('Statut de la fiche.', 'docalist-data'),
        ];
    }

    public function getFormattedValue($options = null): string
    {
        $value = $this->getPhpValue();
        $status = get_post_status_object($value);

        return $status ? $status->label : $value;
    }

    /**
     * Retourne la liste des statuts de publication WordPress.
     *
     * @return array Un tableau de la forme [statut => libellé].
     */
    protected function getEntries(): array
    {
        return get_post_statuses();
    }
}
