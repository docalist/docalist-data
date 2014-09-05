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
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Object;
use Docalist\Schema\Field;

/**
 * Une traduction du titre original du document.
 *
 * @property String $language
 * @property String $title
 */
class Translation extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'language' => [
                    'label' => __('Langue', 'docalist-biblio'),
                ],
                'title' => [
                    'label' => __('Titre traduit', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['translation'][] = $this->title();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['translation'] = self::stdIndex(true);
    }
}