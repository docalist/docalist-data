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
use Docalist\Data\Export\Exporter\StandardExporter;
use Docalist\Data\Record;
use Docalist\Data\Export\Converter;
use Docalist\Data\Export\Writer;

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
    protected function createExporter()
    {
        return new class() extends StandardExporter
        {
            public function __construct()
            {
                // Record filter : transforme le premier enregistrement en tout maju et supprime les suivants
                $before = function (Record $record)
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
                };

                // Convertisseur
                $converter = new class implements Converter
                {
                    public function getSupportDescription(): string
                    {
                        return 'Tous les types';
                    }

                    public function supports(string $className): bool
                    {
                        return true;
                    }

                    public function __invoke(Record $record)
                    {
                        return $record->getPhpValue() + ['status' => 'CCC'];
                    }
                };

                // Data filter : met la premiere lettre de chaque champ en maju
                $after = function (array $data)
                {
                    return array_map('ucfirst', $data);
                };

                // Un Writer qui fait juste un var_export()
                $writer = new class() implements Writer
                {
                    public function getContentType(): string
                    {
                        return 'mime/type';
                    }

                    public function isBinaryContent(): bool
                    {
                        return true;
                    }

                    public function suggestFilename(): string
                    {
                        return 'writer.ext2';
                    }

                    public function export(Iterable $records)
                    {
                        var_export(iterator_to_array($records));
                    }
                };

                parent::__construct($converter, $writer);
                $this->prependOperation($before);
                $this->appendOperation($after);
            }

            public static function getID(): string
            {
                return 'my-ID';
            }

            public static function getLabel(): string
            {
                return 'my-label';
            }

            public static function getDescription(): string
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
        ob_start();
        $exporter->export([new Record(['posttitle' => 'AAA', 'slug' => 'BBB']), new Record()]);
        $result = ob_get_clean();

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
        $this->assertSame(true, $exporter->isBinaryContent());
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
}
