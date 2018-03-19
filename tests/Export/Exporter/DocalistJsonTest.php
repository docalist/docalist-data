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

use WP_UnitTestCase;
use Docalist\Data\Export\Exporter\DocalistJson;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Export\DataProcessor\RemoveEmptyFields;
use Docalist\Data\Export\DataProcessor\RemoveEmptyRecords;
use Docalist\Data\Export\DataProcessor\SortFields;
use Docalist\Data\Export\Writer\JsonWriter;
use Docalist\Data\Entity\ContentEntity;

/**
 * Teste l'export Docalist au format JSON.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJsonTest extends WP_UnitTestCase
{
    /**
     * Teste que l'exporteur est correctement construit.
     */
    public function testConstruct()
    {
        $exporter = new DocalistJson();

        $this->assertEmpty($exporter->getRecordProcessors());

        $this->assertInstanceOf(DocalistConverter::class, $exporter->getConverter());

        $processors = $exporter->getDataProcessors();
        $this->assertCount(3, $processors);
        $this->assertInstanceOf(RemoveEmptyFields::class, $processors[0]);
        $this->assertInstanceOf(RemoveEmptyRecords::class, $processors[1]);
        $this->assertInstanceOf(SortFields::class, $processors[2]);

        $this->assertInstanceOf(JsonWriter::class, $exporter->getWriter());
    }

    /**
     * Teste la méthode getID().
     */
    public function testGetID()
    {
        $this->assertSame('docalist-json', DocalistJson::getID());
    }

    /**
     * Teste la méthode getLabel().
     */
    public function testGetLabel()
    {
        $this->assertSame('Docalist JSON', DocalistJson::getLabel());
    }

    /**
     * Teste la méthode getDescription().
     */
    public function testGetDescription()
    {
        $this->assertNotEmpty(DocalistJson::getDescription());
    }

    /**
     * Teste la méthode getDescription().
     */
    public function testSuggestFilename()
    {
        $exporter = new DocalistJson();
        $this->assertSame('docalist-export.json', $exporter->suggestFilename());
    }

    /**
     * Teste la méthode exportToString() et, indirectement, la méthode export().
     */
    public function testExportToString()
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

        // Détermine le résultat attendu
        $expected = '[' .
            // Enregistrement 1
            '{"content":[{"type":"content","value":"content"}],"posttitle":"Welcome","slug":"test"},' .

            // L'enregistrement vide a été supprimé

            // Enregistrement 2
            '{"slug":"test2"}' .
        ']';

        // Exporte les enregistrements et vérifie qu'on a le résultat attendu
        $exporter = new DocalistJson();
        $this->assertSame($expected, $exporter->exportToString([$record1, $empty, $record2]));
    }
}
