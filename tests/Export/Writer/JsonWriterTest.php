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

namespace Docalist\Data\Tests\Export\Writer;

use PHPUnit_Framework_TestCase;
use Docalist\Data\Export\Writer\JsonWriter;

/**
 * Teste la classe JsonWriter.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JsonWriterTest extends PHPUnit_Framework_TestCase
{
    public function testPretty()
    {
        $writer = new JsonWriter();

        $this->assertFalse($writer->getPretty());

        $writer->setPretty(true);
        $this->assertTrue($writer->getPretty());

        $writer->setPretty(false);
        $this->assertFalse($writer->getPretty());
    }

    public function testGetContentType()
    {
        $writer = new JsonWriter();
        $this->assertSame('application/json; charset=utf-8', $writer->getContentType());
    }

    public function testIsBinaryContent()
    {
        $writer = new JsonWriter();
        $this->assertFalse($writer->isBinaryContent());
    }

    public function testSuggestFilename()
    {
        $writer = new JsonWriter();
        $this->assertSame('export.json', $writer->suggestFilename());
    }

    public function testExport()
    {
        $writer = new JsonWriter();
        $export = function ($records) use($writer) {
            ob_start();
            $writer->export($records);
            return ob_get_clean();
        };

        // Mode compact
        $this->assertSame('[{"a":"A"}]', $export([['a'=>'A']]));

        // Mode pretty
        $writer->setPretty(true);
        $this->assertSame("[\n{\n    \"a\": \"A\"\n}\n]", $export([['a'=>'A']]));

        // Vérifie que accents, slashs sont non échappés
        $writer->setPretty(false);
        $this->assertSame('[{"é":"/"}]', $export([['é'=>'/']]));
        // et non pas '[{"\u00e9":"\/"}]'
    }
}
