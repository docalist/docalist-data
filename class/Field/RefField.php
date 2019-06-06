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

use Docalist\Type\Integer;
use Docalist\Data\Indexable;
use Docalist\Data\Indexer;
use Docalist\Data\Indexer\RefFieldIndexer;

/**
 * Champ docalist standard "ref" : numéro de l'enregistrement.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RefField extends Integer implements Indexable
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'ref',
            'label' => __('Numéro de fiche', 'docalist-data'),
            'description' => __(
                'Numéro unique attribué par docalist pour identifier la fiche au sein de la collection.',
                'docalist-data'
            ),

            'index' => [
                'search' => true,   // indexation : 'ref' est toujours généré (cf. RefFieldIndexer)
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return RefFieldIndexer::class;
    }
}
