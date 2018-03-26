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
 * Interface d'un convertisseur utilisé pour l'import/export.
 *
 * Cette interface permet de marquer explicitement les filtres qui convertissent les enregistrements Docalist
 * en tableaux de données.
 *
 * Elle contient une seule méthode (__invoke) qui prend en paramètre un objet Record et retourne un tableau
 * contenant les données à exporter. La méthode peut aussi supprimer certains enregsitrements en retournant
 * null à la place des données.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
/**
 * Interface des convertisseurs utilisés pour l'export de données Docalist.
 *
 * Un convertisseur se charge de transformer un enregistrement Docalist dans un autre format.
 *
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
    public function __invoke(Record $record);
}
