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

use Docalist\Data\Export\Writer;

/**
 * Interface d'un exporteur Docalist.
 *
 * Un exporteur convertit des enregistrements Docalist et les écrit dans un flux de sortie.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Exporter extends Writer
{
    /**
     * Retourne un identifiant unique pour l'exporteur.
     *
     * @return string
     */
    public static function getID(): string;

    /**
     * Retourne le libellé de l'exporteur.
     *
     * @return string
     */
    public static function getLabel(): string;

    /**
     * Retourne la description de l'exporteur.
     *
     * @return string
     */
    public static function getDescription(): string;

    /**
     * Indique si l'exporteur supporte le type d'enregistrement Docalist passé en paramètre.
     *
     * @param string $className Nom complet d'une classe PHP qui représente un type d'enregistrement Docalist
     * (le nom d'un classe qui hérite de 'Docalist\Data\Record').
     *
     * @return bool Retourne true si l'exporteur sait exporteur les enregistrements de ce type, false sinon.
     */
    public function supports(string $className): bool;
}
