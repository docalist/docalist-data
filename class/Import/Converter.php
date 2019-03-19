<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Import;

use Docalist\Data\Record;
use Generator;

/**
 * Interface d'un convertisseur utilisé pour l'import.
 *
 * Un convertisseur est un callable qui convertit un tableau contenant des données en enregistrement Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Converter
{
    /**
     * Convertit les données passées en paramètre en enregistrement Docalist.
     *
     * @param array $data Les données à convertir.
     *
     * @return Record|null|Generator Retourne :
     *
     * - soit un objet Record contenant l'enregistrement Docalist à importer,
     * - soit null pour empêcher l'import des données passées en paramètre (i.e. ignorer un enregistrement),
     * - soit un générateur pour créer plusieurs Record Docalist à partir des données fournies.
     */
    public function __invoke(array $data);
}
