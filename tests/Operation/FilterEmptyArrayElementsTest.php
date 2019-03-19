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

namespace Docalist\Data\Tests\Operation;

use PHPUnit_Framework_TestCase;
use Docalist\Data\Operation\FilterEmptyArrayElements;

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

        // Vérifie que le filter est un callable
        $this->assertTrue(is_callable($filter));

        // Vérifie que le filter retourne bien le résultat attendu
        $this->assertSame($result, $filter($data));
    }
}
