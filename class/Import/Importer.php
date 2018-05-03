<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Import;

/**
 * Interface d'un importeur Docalist.
 *
 * Un importeur charge des données depuis un fichier et les convertit en enregistrements Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Importer extends Reader
{
    /**
     * Retourne un identifiant unique pour l'importeur.
     *
     * @return string
     */
    public static function getID(): string;

    /**
     * Retourne le libellé de l'importeur.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Retourne la description de l'importeur.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Charge des données depuis le fichier indiqué et les convertit en enregistrements Docalist.
     *
     * @param string $filename Nom du fichier à charger.
     *
     * @eturn Iterable Les enregistrements Docalist obtenus.
     */
    public function getRecords(string $filename): Iterable;
}
