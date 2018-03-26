<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Filter;

/**
 * Interface d'un filtre utilisé pour l'import/export.
 *
 * Cette interface permet de marquer explicitement les filtres implémentés sous forme de classe.
 *
 * Elle contient une seule méthode (__invoke) qui applique une transformation sur les données qu'on lui passe
 * et retourne les données modifiées. La méthode peut aussi supprimer certaines données de la chaine en retournant
 * null à la place des données.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Filter
{
    /**
     * Filtre les données passées en paramètre.
     *
     * @param mixed $data Les données à traiter.
     *
     * @return mixed|null Les données modifiées ou null pour filtrer les données.
     */
    public function __invoke($data);
}
