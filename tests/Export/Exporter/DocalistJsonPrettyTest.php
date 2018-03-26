<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Tests\Export\Exporter;

use Docalist\Data\Tests\Export\Exporter\DocalistJsonTest;
use Docalist\Data\Export\Exporter\DocalistJsonPretty;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Export\Writer\JsonWriter;

/**
 * Teste l'export Docalist au format JSON Pretty.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJsonPrettyTest extends DocalistJsonTest
{
    const EXPORTER = DocalistJsonPretty::class;
    const EXPECTED_ID = 'docalist-json-pretty';
    const EXPECTED_CONVERTER = DocalistConverter::class;
    const EXPECTED_WRITER = JsonWriter::class;
    const EXPECTED_FILENAME = 'docalist-json-pretty-export.json';

    protected function getExpectedExport()
    {
        $lines = [
            '[',
            // Enregistrement 1
            '{',
            '    "content": [',
            '        {',
            '            "type": "content",',
            '            "value": "content"',
            '        }',
            '    ],',
            '    "posttitle": "Welcome",',
            '    "slug": "test"',
            '},',

            // L'enregistrement vide a été supprimé

            // Enregistrement 2
            '{',
            '    "slug": "test2"',
            '}',
            ']',
        ];

        return implode("\n", $lines);
    }
}
