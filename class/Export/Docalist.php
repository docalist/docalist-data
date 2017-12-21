<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Export;

/**
 * Convertisseur "Docalist".
 *
 * Ne fait rien, retourne les notices au format natif de docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Docalist extends Converter
{
    public function getLabel()
    {
        return __('Format docalist', 'docalist-databases');
    }

    public function getDescription()
    {
        return __('Notices au format natif de Docalist-Databases.', 'docalist-databases');
    }
}
