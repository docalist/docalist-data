<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Data\Export\Writer;

use Docalist\Data\Export\Writer;

/**
 * Générateur JSON pour l'export de données Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JsonWriter implements Writer
{
    /**
     * Génère ou non du JSON indenté et formatté.
     *
     * @var bool
     */
    protected $pretty = false;

    /**
     * Modifie l'option "pretty" qui indique s'il faut génèrer ou non du JSON indenté et formatté.
     *
     * @param bool $pretty
     *
     * @return self
     */
    public function setPretty(bool $pretty): self
    {
        $this->pretty = (bool) $pretty;

        return $this;
    }

    /**
     * Indique si on génère ou non du JSON indenté et formatté.
     *
     * @return boolean
     */
    public function getPretty(): bool
    {
        return $this->pretty;
    }

    public function getContentType(): string
    {
        return 'application/json; charset=utf-8';
    }

    public function isBinaryContent(): bool
    {
        return false;
    }

    public function suggestFilename(): string
    {
        return 'export.json';
    }

    public function export(Iterable $records)
    {
        $pretty = $this->getPretty();

        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $pretty && $options |= JSON_PRETTY_PRINT;

        $first = true;
        $comma = $pretty ? ",\n" : ',';
        echo $pretty ? "[\n" : '[';
        foreach ($records as $record) {
            $first ? ($first = false) : print($comma);
            echo json_encode($record, $options);
        }
        echo $pretty ? "\n]" : ']';
    }
}
