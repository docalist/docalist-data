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

namespace Docalist\Data\Tests\Export\Exporter;

use Docalist\Test\DocalistTestCase;
use PHPUnit_Framework_TestCase;
use Docalist\Data\Export\Exporter\DocalistJson;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Export\Writer\JsonWriter;
use Docalist\Data\Entity\ContentEntity;
use Docalist\Data\Record;
use Docalist\Data\Export\Exporter\StandardExporter;

/**
 * Teste l'export Docalist au format JSON.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJsonTest extends DocalistTestCase
{
    const EXPORTER = DocalistJson::class;
    const EXPECTED_ID = 'docalist-json';
    const EXPECTED_CONVERTER = DocalistConverter::class;
    const EXPECTED_WRITER = JsonWriter::class;
    const EXPECTED_FILENAME = 'docalist-json-export.json';

    /**
     * Crée l'exporteur à tester.
     *
     * @return StandardExporter
     */
    protected function createExporter(): StandardExporter
    {
        $class = static::EXPORTER;
        return new $class();
    }

    /**
     * Teste que l'exporteur est correctement construit.
     */
    public function testConstruct()
    {
        $exporter = $this->createExporter();

        $this->assertInstanceOf(static::EXPECTED_CONVERTER, $exporter->getConverter());
        $this->assertInstanceOf(static::EXPECTED_WRITER, $exporter->getWriter());
    }

    /**
     * Teste la méthode getID().
     */
    public function testGetID()
    {
        $class = static::EXPORTER;
        $this->assertSame(static::EXPECTED_ID, $class::getID());
    }

    /**
     * Teste la méthode getLabel().
     */
    public function testGetLabel()
    {
        $this->assertNotEmpty($this->createExporter()->getLabel());
    }

    /**
     * Teste la méthode getDescription().
     */
    public function testGetDescription()
    {
        $this->assertNotEmpty($this->createExporter()->getDescription());
    }

    /**
     * Teste la méthode suggestFilename().
     */
    public function testSuggestFilename()
    {
        $exporter = $this->createExporter();
        $this->assertSame(static::EXPECTED_FILENAME, $exporter->suggestFilename());
    }

    /**
     * Retourne les enregistrements à exporter.
     *
     * @return Record[]
     */
    protected function getRecordsToExport()
    {
        // Enregistrement 1
        $record1 = new ContentEntity([
            'slug' => 'test',
            'posttitle' => 'Welcome',
            'content' => [ ['type' => 'content', 'value' => 'content'] ],
        ]);

        // Enregistrement vide
        $empty = new ContentEntity();
        unset($empty->content);

        // Enregistrement 2
        $record2 = new ContentEntity([
            'slug' => 'test2',
        ]);

        return [$record1, $empty, $record2];
    }

    /**
     * Retourne l'export que l'on doit obtenir.
     *
     * @return string
     */
    protected function getExpectedExport()
    {
        return '[' .
            // Enregistrement 1
            '{"content":[{"type":"content","value":"content"}],"posttitle":"Welcome","slug":"test"},' .

            // L'enregistrement vide a été supprimé

            // Enregistrement 2
            '{"slug":"test2"}' .
        ']';
    }

    /**
     * Teste la méthode export().
     */
    public function testExport()
    {
        $exporter = $this->createExporter();
        ob_start();
        $exporter->export($this->getRecordsToExport());
        $result = ob_get_clean();
        $this->assertSame($this->getExpectedExport(), $result);
    }
}
