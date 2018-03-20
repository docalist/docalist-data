<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Writer;

use Docalist\Data\Export\Writer;

/**
 * Classe de base pour les Writers.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class AbstractWriter implements Writer
{
    public final function exportToString(Iterable $records)
    {
        // Ouvre un flux en mémoire mémoire
        $stream = fopen('php://temp', 'r+');

        // Exporte les enregistrements
        $this->export($stream, $records);

        // Rembobine le flux et récupère tout ce qui a été généré
        rewind($stream);
        $result = stream_get_contents($stream);

        // Ferme le flux
        fclose($stream);

        // Retourne le résultat
        return $result;
    }
}
