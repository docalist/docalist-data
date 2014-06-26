<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Data\Entity\AbstractEntity;

/**
 * Organisme.
 *
 * @property string $name
 * @property string $acronym
 * @property string $city
 * @property string $country
 * @property string $role
 */
class Organisation extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __("Nom de l'organisme", 'docalist-biblio'),
            ),
            'acronym' => array(
                'label' => __('Sigle', 'docalist-biblio'),
                'description' => __("Sigle ou acronyme", 'docalist-biblio'),
            ),
            'city' => array(
                'label' => __('Ville', 'docalist-biblio'),
                'description' => __('Ville du siège social', 'docalist-biblio'),
            ),
            'country' => array(
                'label' => __('Pays', 'docalist-biblio'),
                'description' => __('Pays du siège social', 'docalist-biblio'),
            ),
            'role' => array(
                'label' => __('Rôle', 'docalist-biblio'),
                'description' => __('Fonction', 'docalist-biblio'),
            )
        );
        // @formatter:on
    }

    public function __toString() {
        $result = $this->name;

        if ($this->acronym) {
            $result .= ' - ';
            $result .= $this->acronym;
        }

        if ($this->city || $this->country) {
            $result .= ' (';
            $this->city && $result .= $this->city;
            if ($this->country) {
                $this->city && $result .= ', ';
                $result .= $this->country;
            }
            $result .= ')';
        }

        $this->role && $result .= ' / ' . $this->role;

        return $result;
    }
}