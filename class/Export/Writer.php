<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Data\Export;

/**
 * Interface des générateurs utilisés pour l'export de données Docalist.
 *
 * Un générateur se charge d'écrire les données des enregistrements exportés dans un flux de sortie.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Writer
{
    /**
     * Retourne le type MIME du contenu généré.
     *
     * @return string Retourne un type MIME (par exemple "text/plain; charset=utf-8").
     */
    public function getContentType(): string;

    /**
     * Indique si le contenu généré est binaire.
     *
     * @return bool Retourne true si le contenu généré est binaire, false s'il peut être affiché.
     */
    public function isBinaryContent(): bool;

    /**
     * Suggère un nom pour le fichier généré.
     *
     * @return string Un nom de fichier avec une extension (par exemple "export.txt").
     */
    public function suggestFilename(): string;

    /**
     * Exporte les enregistrements passés en paramètre.
     *
     * @param Iterable $records Les enregistrements à exporter.
     */
    public function export(Iterable $records);
}
