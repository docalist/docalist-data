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

use Docalist\Data\Export\Converter\WriteError;

/**
 * Interface des générateurs utilisés pour l'export de données Docalist.
 *
 * Un générateur se charge d'écrire les données des enregistrements exportés dans un flux de sortie.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface ExportWriter
{
    /**
     * Retourne le type MIME du contenu généré.
     *
     * @return string Retourne un type MIME (par exemple "text/plain; charset=utf-8").
     */
    public function getContentType();

    /**
     * Indique si le contenu généré est binaire.
     *
     * @return bool Retourne true si le contenu généré est binaire, false s'il peut être affiché.
     */
    public function isBinaryContent();

    /**
     * Suggère un nom pour le fichier généré.
     *
     * @return string Un nom de fichier avec une extension (par exemple "export.txt").
     */
    public function suggestFilename();

    /**
     * Exporte les enregistrements passés en paramètre dans le flux de sortie indiqué.
     *
     * @param Resource $stream  Le flux de sortie où écrire les données.
     * @param Iterable $records Les enregistrements à exporter.
     *
     * @throw WriteError Si une erreur survient lors de l'écriture des données.
     */
    public function export($stream, Iterable $records);
}
