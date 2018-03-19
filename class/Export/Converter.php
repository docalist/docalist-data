<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export;

use Docalist\Data\Record;

/**
 * Interface des convertisseurs utilisés pour l'export de données Docalist.
 *
 * Un convertisseur se charge de transformer un enregistrement Docalist dans un autre format.
 *
 * Il prend en paramètre un objet Record et retourne un tableau contenant les données à exporter.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Converter
{
    /**
     * Convertit un enregistrement Docalist.
     *
     * @param Record $record L'enregistrement Docalist à convertir.
     *
     * @return array|null Un tableau contenant les données à exporter ou null si l'enregistrement ne peut pas
     * être converti.
     */
    public function convert(Record $record);
}
