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
use Docalist\Data\Export\DataProcessor\RemoveEmptyFields;

/**
 * Teste le processeur RemoveEmptyFields.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveEmptyFieldsTest extends PHPUnit_Framework_TestCase
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
     * Teste le processeur.
     *
     * @param array $data
     * @param array $result
     *
     * @dataProvider dataProvider
     */
    public function testProcess(array $data, array $result)
    {
        $processor = new RemoveEmptyFields();

        $this->assertSame($result, $processor->process($data));
    }
}
