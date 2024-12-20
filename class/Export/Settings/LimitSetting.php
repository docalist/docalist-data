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

namespace Docalist\Data\Export\Settings;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\Integer as DocalistInteger;

/**
 * Options de configuration du plugin.
 *
 * @property Text       $role   Rôle Wordpress.
 * @property DocalistInteger    $limit  Limite pour ce rôle.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LimitSetting extends Composite
{
    public static function loadSchema(): array
    {
        return [
            'fields' => [
                'role' => [
                    'type' => Text::class,
                    'label' => __('Rôle WordPress', 'docalist-data'),
                    'description' => __("Groupe d'utilisateurs", 'docalist-data'),
                ],
                'limit' => [
                    'type' => DocalistInteger::class,
                    'label' => __('Limite pour ce rôle', 'docalist-data'),
                    'description' => __('Nombre maximum de notices exportables.', 'docalist-data'),
                ],
            ],
        ];
    }

    public function filterEmpty(bool $strict = true): bool
    {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de rôle ou que la limite est à zéro
        return $this->filterEmptyProperty('role') || 0 === $this->limit->getPhpValue();
    }
}
