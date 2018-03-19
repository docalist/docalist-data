<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\DataProcessor;

use Docalist\Data\Export\DataProcessor;

/**
 * Trie les champs des données exportées par ordre alphabétique.
 *
 * Permet de générer un fichier d'export dans lequel les champs sont toujours dans le même ordre.
 * Seuls les champs sont triés, les sous-champs ne sont pas modifiés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SortFields implements DataProcessor
{
    public function process(array $data)
    {
        ksort($data);

        return $data;
    }
}
