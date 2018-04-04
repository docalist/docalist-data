<?php declare(strict_types=1);
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
use InvalidArgumentException;
use RuntimeException;

/**
 * Classe de base pour les Writers.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class AbstractWriter implements Writer
{
    final public function exportToString(Iterable $records): string
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

    /**
     * Vérifie que le paramètre est un flux ouvert en écriture, génère une exception sinon.
     *
     * @param mixed $stream Flux à tester.
     *
     * @throws InvalidArgumentException
     */
    protected function checkIsWritableStream($stream)
    {
        // Génère une exception si ce n'est pas un stream
        if ( ! is_resource($stream)) {
            throw new InvalidArgumentException('Invalid stream, not a resource');
        }

        // Récupère le mode d'ouverture du flux
        $meta = stream_get_meta_data($stream);
        $mode = $meta['mode'];

        // Liste des modes qui permettent d'ouvrir un fichier en écriture
        $writable = [ // credits : guzzle/Stream (https://github.com/guzzle/streams/blob/master/src/Stream.php)
            'w'     => true, 'w+'   => true, 'rw'   => true, 'r+'   => true, 'x+' => true,
            'c+'    => true, 'wb'   => true, 'w+b'  => true, 'r+b'  => true,
            'x+b'   => true, 'c+b'  => true, 'w+t'  => true, 'r+t'  => true,
            'x+t'   => true, 'c+t'  => true, 'a'    => true, 'a+'   => true,
        ];

        // Génère une exception si le flux n'a pas été ouvert en écriture
        if (! isset($writable[$mode])) {
            throw new InvalidArgumentException('Stream is not writable');
        }
    }

    /**
     * Ecrit des données dans le flux et génère une exception en cas d'erreur.
     *
     * @param resource $stream
     * @param string  $data
     *
     * @throws RuntimeException
     */
    protected function write($stream, string $data)
    {
        error_clear_last();
        $size = fwrite($stream, $data);
        if ($size === false) {
            $message = 'Write error during export';
            $error = error_get_last();
            isset($error['message']) && $message .= ': ' . $error['message'];
            throw new RuntimeException($message);
        }
    }
}
