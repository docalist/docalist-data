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
use Docalist\Data\Export\DataProcessor\SortFields;

/**
 * Teste le processeur SortFields.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SortFieldsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Fournit des exemples de données à traiter et le résultat attendu.
     *
     * @return array[]
     */
    public function dataProvider()
    {
        return [
            // Trie les noms de champs par ordre alpha
            [
                ['b' => 'B', 'a' => 'A'],
                ['a' => 'A', 'b' => 'B']
            ],

            // Ne tient pas compte de la casse des caractères
            [
                ['b' => 'B', 'C' => 'C'],
                ['C' => 'C', 'b' => 'B']
            ],

            // Ne trie pas les sous-champs
            [
                ['b' => ['b2' => 'B2', 'b1' => 'B1'], 'a' => ['a2' => 'A2', 'a1' => 'A1']],
                ['a' => ['a2' => 'A2', 'a1' => 'A1'], 'b' => ['b2' => 'B2', 'b1' => 'B1']],
            ],
        ];
    }

    /**
     * Teste le processeur.
     *
     * @param array $data
     * @param array|null $result
     *
     * @dataProvider dataProvider
     */
    public function testProcess(array $data, $result)
    {
        $processor = new SortFields();

        $this->assertSame($result, $processor->process($data));
    }
}
