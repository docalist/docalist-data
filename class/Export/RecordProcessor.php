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
 * Interface des pré-processeurs utilisés pour l'export de données Docalist.
 *
 * Un RecordProcessor traite les enregistrements Docalist à exporter avant qu'il ne soient convertis.
 *
 * Il peut modifier l'objet Record passé en paramètre et filtrer les enregistrements qui ne doivent pas être
 * exportés en retournant null.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface RecordProcessor
{
    /**
     * Traite l'enregistrement Docalist passé en paramètre.
     *
     * @param Record $record L'enregistrement Docalist à traiter.
     *
     * @return Record|null L'enregistrement modifié ou null si l'enregistrement ne doit pas être exporté.
     */
    public function process(Record $record);
}
