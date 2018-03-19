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
 * Un exporteur au format JSON.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JsonWriter implements Writer
{
    protected static $defaultSettings = [
        // Surcharge les paramètres hérités
        'mime-type' => 'application/json',
        'extension' => '.json',

        // Indique s'il faut générer du code lisible ou indenté
        'pretty' => false,
    ];

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
        $pretty = false; //$this->get('pretty');
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $pretty && $options |= JSON_PRETTY_PRINT;

        $first = true;
        fwrite($stream, '[');
        $pretty && fwrite($stream, "\n");
        $comma = $pretty ? ",\n" : ',';
        foreach ($records as $record) {
            $data = $this->converter->convert($record);
            $data = $this->removeEmpty($data);
            if (empty($data)) {
                continue;
            }
            $first ? ($first = false) : print($comma);
            fwrite($stream, json_encode($data, $options));
            $pretty && fwrite($stream, "\n");
        }
        echo ']';
        $pretty && fwrite($stream, "\n");
    }

    protected function removeEmpty($data)
    {
        return array_filter($data, function ($value) {
            is_array($value) && $value = $this->removeEmpty($value);

            return ! ($value === '' | $value === null | $value === []);
        });
    }
}
