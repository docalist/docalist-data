<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Transform;

/**
 * Interface de base des objets "transformer".
 *
 * Un Transformer applique une transformation sur les données qu'on lui passe et retourne les données modifiées.
 *
 * Il peut aussi filtrer les données traitées en retournant null à la place des données modifiées.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Transformer
{
    /**
     * Transforme ou filtre les données passées en paramètre.
     *
     * @param array $data Les données à traiter.
     *
     * @return mixed|null Les données modifiées ou null pour filtrer les données.
     */
    public function transform($data);
}
