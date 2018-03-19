<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\RecordConverter;

use Docalist\Data\Export\RecordConverter;
use Docalist\Data\Record;

/**
 * Convertisseur Docalist pour l'export de données Docalist.
 *
 * Retourne les données brutes présentes dans l'enregistrement Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Docalist implements RecordConverter
{
    public function convert(Record $record)
    {
        return $record->getPhpValue();
    }
}
