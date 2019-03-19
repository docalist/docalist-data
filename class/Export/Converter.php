<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Export;

use Docalist\Data\Record;
use Generator;

/**
 * Interface d'un convertisseur utilisé pour l'export.
 *
 * Un convertisseur est un callable qui convertit un enregistrement Docalist en tableau de données.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Converter
{
    /**
     * Indique si le convertisseur supporte le type d'enregistrement Docalist passé en paramètre.
     *
     * @param string $className Nom complet d'une classe PHP qui représente un type d'enregistrement Docalist
     * (le nom d'un classe qui hérite de 'Docalist\Data\Record').
     *
     * @return bool Retourne true si l'exporteur sait exporteur les enregistrements de ce type, false sinon.
     */
    public function supports(string $className): bool;

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
