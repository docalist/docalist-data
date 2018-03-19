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
use Docalist\Data\Record;
use Docalist\Data\Export\Converter\DocalistConverter;

/**
 * Teste le processeur SortFields.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Teste le convertisseur.
     */
    public function testProcess()
    {
        $converter = new DocalistConverter();

        $record = new Record();
        $this->assertSame([], $converter->convert($record));

        $data = ['posttype' => 'record', 'posttitle' => 'titre'];
        $record = new Record($data);
        $this->assertSame($data, $converter->convert($record));
    }
}
