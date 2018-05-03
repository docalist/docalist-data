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
 * Interface des Readers utilisés pour l'import de données Docalist.
 *
 * Un Reader lit des données depuis un fichier et retourne une liste d'enregistrements.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Reader
{
    /**
     * Charge des données depuis un fichier et retourne les enregistrements qu'il contient.
     *
     * Les enregistrements sont retournés tels quels, sans conversion.
     *
     * @param string $filename Le path du fichier à lire.
     *
     * @return Iterable Les enregistrements présents dans le fichier.
     */
    public function getRecords(string $filename): Iterable;
}
