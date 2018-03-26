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
     * L'exporteur est constitué uniquement de classes et fonctions anonymes dont on peut tester les résultats.
     *
     * @return StandardExporter
     */
    protected function createExporter($withBadFilter = false)
    {
        return new class($withBadFilter) extends StandardExporter
        {
            public function __construct($withBadFilter = false)
            {
                $filters = [
                    // Record filter : transforme le premier enregistrement en tout maju et supprime les suivants
                    function (Record $record)
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
                    },

                    // Convertisseur : convertit le record en array et ajoute un champ 'status'='CCC'
                    function (Record $record)
                    {
                        return $record->getPhpValue() + ['status' => 'CCC'];
                    },

                    // Data filter : met la premiere lettre de chaque champ en maju
                    function (array $data)
                    {
                        return array_map('ucfirst', $data);
                    }
                ];

                if ($withBadFilter) {
                    $filters[] = 'hello';
                }

                // Un Writer qui fait juste un var_export()
                $writer = new class() extends AbstractWriter
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

                parent::__construct($filters, $writer);
            }

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
        };
    }

    /**
     * Teste la méthode exportToString.
     *
     * Indirectement, on teste énormément de choses de StandardExporter :
     *
     * - on vérifie que __construct() a bien initialisé nos processeurs/converteur/writer
     * - on vérifie que export() exécute correctement le pipeline
     * - on vérifie que convert() gère correctement les filtres qui retournent null
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
        $this->assertSame('my-ID-writer.ext2', $exporter->suggestFilename());
    }


    /**
     * Vérifie qu'une exception est générée si on passe un filtre qui n'est pas un callable.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage not callable
     */
    public function testInvalidFilter()
    {
        $this->createExporter(true);
    }
}
