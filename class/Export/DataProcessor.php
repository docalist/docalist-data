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

/**
 * Interface des post-processeurs utilisés pour l'export de données Docalist.
 *
 * Un DataProcessor traite les enregistrements Docalist à exporter après leur conversion.
 *
 * Il peut modifier les données qui seront exportées ou filtrer les enregistrements qui ne doivent pas être
 * exportés en retournant null.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface DataProcessor
{
    /**
     * Traite les données à exporter passées en paramètre.
     *
     * @param array $data Les données de l'enregistrement à traiter.
     *
     * @return array|null Les données modifiées ou null si l'enregistrement ne doit pas être exporté.
     */
    public function process(array $data);
}
