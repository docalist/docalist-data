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

use Docalist\Data\Export\ExportWriter;

/**
 * Interface d'un exporteur Docalist.
 *
 * Un exporteur convertit des enregistrements Docalist et les écrit dans un flux de sortie.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Exporter extends ExportWriter
{
    /**
     * Retourne un identifiant unique pour l'exporteur.
     *
     * @return string
     */
    public static function getID();

    /**
     * Retourne le libellé de l'exporteur.
     *
     * @return string
     */
    public static function getLabel();

    /**
     * Retourne la description de l'exporteur.
     *
     * @return string
     */
    public static function getDescription();
}
