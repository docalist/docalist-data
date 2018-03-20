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

use PHPUnit_Framework_TestCase;
use Docalist\Data\Export\Writer\XmlWriter;
use Docalist\Data\Export\Exception\WriteError;

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

        $writer->setIndent('2'); // int like
        $this->assertSame(2, $writer->getIndent());

        $writer->setIndent('aa'); // not int
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

    public function testExportToString()
    {
        // Mode compact
        $writer = new XmlWriter();
        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a>A</a></record></records>\n",
            $writer->exportToString([['a'=>'A']])
        );

        // Mode indenté
        $writer->setIndent(2);
        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records>\n  <record>\n    <a>A</a>\n  </record>\n</records>\n",
            $writer->exportToString([['a'=>'A']])
        );
    }

    public function testOutputArray()
    {
        $writer = new XmlWriter();
        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a>A</a></record></records>\n",
            $writer->exportToString([['a'=>'A']])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a/></record></records>\n",
            $writer->exportToString([['a'=>'']])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a><item>A</item></a></record></records>\n",
            $writer->exportToString([['a'=>['A']]])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a/></record></records>\n",
            $writer->exportToString([['a'=>[]]])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><item>A</item></record></records>\n",
            $writer->exportToString([['A']])
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            "<records><record><a><item>A1</item><key>A2</key></a></record></records>\n",
            $writer->exportToString([['a'=>['A1', 'key' => 'A2']]])
        );
    }

    public function testFlushBuffer()
    {
        $writer = new XmlWriter();

        $records = [];
        $expected = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $expected .= '<records>';
        for ($i = 0; $i < XmlWriter::BUFFER_COUNT; $i++) {
            $records[] = ['a' => ''];
            $expected .= '<record><a/></record>';
        }
        $expected .= '</records>' . "\n";

        $this->assertSame(
            $expected,
            $writer->exportToString($records)
        );
    }

    /**
     * Vérifie qu'une exception WriteError est générée si on ne passe pas un handle de fichier correct à export()
     *
     * @expectedException Docalist\Data\Export\Exception\WriteError
     * @expectedExceptionMessage An error occured during export
     */
    public function testWriteError()
    {
        $writer = new XmlWriter();
        $writer->export(null, [['a'=>'']]);
    }

    /**
     * Vérifie qu'une exception WriteError est générée si on ne passe pas un handle de fichier ouvert en lecture
     *
     * @expectedException Docalist\Data\Export\Exception\WriteError
     * @expectedExceptionMessage An error occured during export
     */
    public function testWriteError2()
    {
        $writer = new XmlWriter();
        $writer->export(fopen('php://temp', 'r'), [['a'=>'']]);
    }
}

