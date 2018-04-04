<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Tests\Export\Exporter;

use PHPUnit_Framework_TestCase;
use Docalist\Data\Export\Writer\XmlWriter;

/**
 * Teste la classe XmlWriter.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class XmlWriterTest extends PHPUnit_Framework_TestCase
{
    public function testIndent()
    {
        $writer = new XmlWriter();

        $this->assertSame(0, $writer->getIndent());

        $writer->setIndent(8);
        $this->assertSame(8, $writer->getIndent());

        $writer->setIndent(0);
        $this->assertSame(0, $writer->getIndent());
    }

    public function testGetContentType()
    {
        $writer = new XmlWriter();
        $this->assertSame('application/xml; charset=utf-8', $writer->getContentType());
    }

    public function testIsBinaryContent()
    {
        $writer = new XmlWriter();
        $this->assertFalse($writer->isBinaryContent());
    }

    public function testSuggestFilename()
    {
        $writer = new XmlWriter();
        $this->assertSame('export.xml', $writer->suggestFilename());
    }

    public function testExport()
    {
        $writer = new XmlWriter();
        $export = function ($records) use($writer) {
            ob_start();
            $writer->export($records);
            return ob_get_clean();
        };

        // Mode compact
        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a>A</a></record></records>\n",
            $export([['a'=>'A']])
        );

        // Mode indenté
        $writer->setIndent(2);
        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records>\n  <record>\n    <a>A</a>\n  </record>\n</records>\n",
            $export([['a'=>'A']])
        );
    }

    public function testOutputArray()
    {
        $writer = new XmlWriter();
        $export = function ($records) use($writer) {
            ob_start();
            $writer->export($records);
            return ob_get_clean();
        };

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a>A</a></record></records>\n",
            $export([['a'=>'A']])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a/></record></records>\n",
            $export([['a'=>'']])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a><item>A</item></a></record></records>\n",
            $export([['a'=>['A']]])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a/></record></records>\n",
            $export([['a'=>[]]])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><item>A</item></record></records>\n",
            $export([['A']])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a><item>A1</item><key>A2</key></a></record></records>\n",
            $export([['a'=>['A1', 'key' => 'A2']]])
        );
    }
}
