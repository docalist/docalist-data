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
use Docalist\Data\Export\DataProcessor\RemoveEmptyRecords;

/**
 * Teste le processeur RemoveEmptyRecords.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveEmptyRecordsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Fournit des exemples de données à traiter et le résultat attendu.
     *
     * @return array[]
     */
    public function dataProvider()
    {
        return [
            [ [     ],  null    ],
            [ [ 'a' ],  [ 'a' ] ],
            [ [ []  ],  [ []  ] ],
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
        $processor = new RemoveEmptyRecords();

        $this->assertSame($result, $processor->process($data));
    }
}
