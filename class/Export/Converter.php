<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Databases\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Export;

use Docalist\Databases\Type;

/**
 * Classe de base pour les convertisseurs.
 *
 * Un convertisseur se charge de transformer une Reference Docalist dans un autre format.
 */
class Converter extends BaseExport
{
    /**
     * Convertit une notice docalist.
     *
     * @param Type $ref La notice à convertir.
     *
     * @return array Un tableau contenant les données à exporter.
     */
    public function convert(Type $ref)
    {
        return $ref->getPhpValue();
    }
}
