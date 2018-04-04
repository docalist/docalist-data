<?php declare(strict_types=1);
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
use Generator;

/**
 * Interface d'un convertisseur utilisé pour l'import/export.
 *
 * Cette interface permet de marquer explicitement une opération qui convertit un enregistrement Docalist
 * en tableau de données.
 *
 * Elle contient une seule méthode (__invoke) qui prend en paramètre un objet Record et qui retourne
 * - soit un tableau contenant les données à exporter,
 * - soit null pour empêcher l'export d'un enregistrement,
 * - soit un générateur pour exporter plusieurs lignes de données à partir d'un enregistrement unique.
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
     * @return array|null|Generator Retourne :
     *
     * - soit un tableau contenant les données à exporter,
     * - soit null pour empêcher l'export d'un enregistrement,
     * - soit un générateur pour exporter plusieurs lignes de données à partir d'un enregistrement unique.
     */
    public function __invoke(Record $record);
}
