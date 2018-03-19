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
 * Générateur JSON pour l'export de données Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JsonWriter extends AbstractWriter
{
    /**
     * Génère ou non du JSON indenté et formatté.
     *
     * @var bool
     */
    protected $pretty;

    /**
     * Initialise le générateur.
     *
     * @param bool $pretty Indique s'il faut génèrer ou non du JSON indenté et formatté (false par défaut).
     */
    public function __construct($pretty = false)
    {
        $this->pretty = (bool) $pretty;
    }

    /**
     * Indique si on génère ou non du JSON indenté et formatté.
     *
     * @return boolean
     */
    public function getPretty()
    {
        return $this->pretty;
    }

    public function getContentType()
    {
        return 'application/json; charset=utf-8';
    }

    public function isBinaryContent()
    {
        return false;
    }

    public function suggestFilename()
    {
        return 'export.json';
    }

    public function export($stream, Iterable $records)
    {
        $pretty = $this->getPretty();

        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $pretty && $options |= JSON_PRETTY_PRINT;

        $first = true;
        fwrite($stream, $pretty ? "[\n" : '[');
        $comma = $pretty ? ",\n" : ',';
        foreach ($records as $record) {
            $first ? ($first = false) : fwrite($stream, $comma);
            fwrite($stream, json_encode($record, $options));
            $pretty && fwrite($stream, "\n");
        }
        fwrite($stream, $pretty ? "]\n" : ']');
    }
}
