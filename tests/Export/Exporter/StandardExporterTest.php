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
use Docalist\Data\Export\Exporter\StandardExporter;
use Docalist\Data\Export\DataProcessor;
use Docalist\Data\Export\Converter;
use Docalist\Data\Export\RecordProcessor;
use Docalist\Data\Record;
use Docalist\Data\Export\Writer\AbstractWriter;

/**
 * Teste la classe StandardExporter.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class StandardExporterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Crée un exporter de test.
     *
     * L'exporteur est constitué uniquement de classes anonymes qui implémentent les interfaces requises et dont
     * on peut tester les résultats.
     *
     * @return StandardExporter
     */
    protected function createExporter()
    {
        return new class() extends StandardExporter
        {
            public static function getID()
            {
                return 'my-ID';
            }

            public static function getLabel()
            {
                return 'my-label';
            }

            public static function getDescription()
            {
                return 'my-desc';
            }

            protected function initRecordProcessors()
            {
                // un RecordProcessor qui transforme tout en maju
                return parent::initRecordProcessors() + [
                    new class() implements RecordProcessor
                    {
                        public function process(Record $record)
                        {
                            static $first = true;

                            if (! $first) {
                                return null;
                            }

                            foreach ($record->getPhpValue() as $field => $value) {
                                $record->$field = strtolower($value);
                            }

                            $first = false;

                            return $record;
                        }
                    }
                ];
            }

            protected function initConverter()
            {
                // Un converter qui retourne toujours ['AAA', 'BBB']
                return new class() implements Converter
                {
                    public function convert(Record $record)
                    {
                        return $record->getPhpValue() + ['status' => 'CCC'];
                    }

                    public function suggestFilename()
                    {
                        return 'converter.ext1';
                    }
                };
            }

            protected function initDataProcessors()
            {
                // Un DataProcessor qui met la première lettre des champs en majuscule
                return parent::initDataProcessors() + [
                    new class() implements DataProcessor
                    {
                        public function process(array $data)
                        {
                            return array_map('ucfirst', $data);
                        }
                    }
                ];
            }

            protected function initWriter()
            {
                // Un Writer qui fait juste un var_export()
                return new class() extends AbstractWriter
                {
                    public function getContentType()
                    {
                        return 'mime/type';
                    }

                    public function isBinaryContent()
                    {
                        return 'special-value';
                    }

                    public function suggestFilename()
                    {
                        return 'writer.ext2';
                    }

                    public function export($stream, Iterable $records)
                    {
                        fputs($stream, var_export(iterator_to_array($records), true));
                    }
                };
            }
        };
    }

    /**
     * Teste la méthode exportToString.
     *
     * Indirectement, on teste énormément de choses de StandardExporter :
     *
     * - on vérifie que __construct() a bien intialisé nos processeurs/converteur/writer
     * - on vérifie que initRecordProcessors() et initDataProcessors() retournent un tableau vide par défaut
     * - on vérifie que export() exécute correctement le pipeline
     * - on vérifie que process traite bien les processeurs qui retournent null
     * - on vérifie qu'on a bien le résultat attendu, généré par notre exporteur bidon
     */
    public function testExportToString()
    {
        $exporter = $this->createExporter();
        $result = $exporter->exportToString([new Record(['posttitle' => 'AAA', 'slug' => 'BBB']), new Record()]);

        // le RecordProcessor met tout en minu et s'arrête au premier enregistrement
        // le Convertor ajout le champ 'status'
        // le DataProcessor fait ucfirst()
        // le writer fait un var_export()

        $expected = var_export([[
            'posttitle' => 'Aaa',
            'slug' => 'Bbb',
            'status' => 'CCC',
        ]], true);

        $this->assertSame($expected, $result);
    }

    /**
     * Vérifie que getContentType() appelle bien la méthode de notre Writer.
     */
    public function testGetContentType()
    {
        $exporter = $this->createExporter();
        $this->assertSame('mime/type', $exporter->getContentType());
    }

    /**
     * Vérifie que isBinaryContent() appelle bien la méthode de notre Writer.
     */
    public function testIsBinaryContent()
    {
        $exporter = $this->createExporter();
        $this->assertSame('special-value', $exporter->isBinaryContent());
    }

    /**
     * Vérifie que suggestFilename() combine les méthodes de notre Converter et de notre Writer.
     */
    public function testSuggestFilename()
    {
        // converter : 'converter.ext1'
        // writer : 'writer.ext2'
        // traitement : filename(converter) + '-' + writer
        // expected : 'converter-writer.ext2'

        $exporter = $this->createExporter();
        $this->assertSame('converter-writer.ext2', $exporter->suggestFilename());
    }
}
