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
use Docalist\Data\Filter\FilterEmptyArrayElements;
use Docalist\Data\Filter\Filter;

/**
 * Teste le filtre FilterEmptyArrayElements.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FilterEmptyArrayElementsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Fournit des exemples de données à traiter et le résultat attendu.
     *
     * @return array[]
     */
    public function dataProvider()
    {
        return [
            [ [ 'a', 'b', 'c'       ],  ['a', 'b', 'c']         ],

            [ [ '', '', ''          ],  []                      ],
            [ [ null, null, null    ],  []                      ],
            [ [ [], [], []          ],  []                      ],

            [ [ 0, false, [0]       ],  [0, false, [0]]         ],

            [ [ ['a'], ['b'], ['c'] ],  [ ['a'], ['b'], ['c'] ] ],
            [ [ [''], [''], ['']    ],  []                      ],
            [ [ [ [ [null ] ] ]     ],  []                      ],
            [ [ [ [ [ [] ] ] ]      ],  []                      ],

            [ 'not array'            ,  'not array'             ],
        ];
    }

    /**
     * Teste le filtre.
     *
     * @param mixed $data
     * @param mixed $result
     *
     * @dataProvider dataProvider
     */
    public function testProcess($data, $result)
    {
        // Crée le filtre
        $filter = new FilterEmptyArrayElements();

        // Vérifie que le filtre a été marqué avec l'interface "Filter"
        $this->assertInstanceOf(Filter::class, $filter);

        // Vérifie que le filter est un callable (redondant avec le test précédent mais ne nuit pas)
        $this->assertTrue(is_callable($filter));

        // Vérifie que le filter retourne bien le résultat attendu
        $this->assertSame($result, $filter($data));
    }
}
