<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Export;

use Docalist\Databases\Record;

/**
 * Classe de base pour les convertisseurs.
 *
 * Un convertisseur se charge de transformer un enregistrement Docalist dans un autre format.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Converter extends BaseExport
{
    /**
     * Convertit un enregistrement Docalist.
     *
     * @param Record $record L'enregistrement Docalist à convertir.
     *
     * @return array Un tableau contenant les données à exporter.
     */
    public function convert(Record $record)
    {
        return $record->getPhpValue();
    }
}
