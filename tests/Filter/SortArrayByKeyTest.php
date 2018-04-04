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
use Docalist\Data\Filter\SortArrayByKey;
use Docalist\Data\Filter\Filter;

/**
 * Teste le filtre SortArrayByKey.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SortArrayByKeyTest extends PHPUnit_Framework_TestCase
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

            // Ne fait rien si on ne lui passe pas un tableau
            [ 'not array',  'not array' ],
            [ ''         ,  ''          ],

        ];
    }

    /**
     * Teste le filter.
     *
     * @param mixed $data
     * @param mixed $result
     *
     * @dataProvider dataProvider
     */
    public function testProcess($data, $result)
    {
        // Crée le filtre
        $filter = new SortArrayByKey();

        // Vérifie que le filtre a été marqué avec l'interface "Filter"
        $this->assertInstanceOf(Filter::class, $filter);

        // Vérifie que le filter est un callable (redondant avec le test précédent mais ne nuit pas)
        $this->assertTrue(is_callable($filter));

        // Vérifie que le filter retourne bien le résultat attendu
        $this->assertSame($result, $filter($data));
    }
}
