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
use Docalist\Data\Export\Exception\WriteError;
use Docalist\Data\Export\ExportException;

/**
 * Teste l'exception WriteError.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class WriteErrorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Vérifie que l'exception a le bon type.
     */
    public function testConstruct()
    {
        $exception = new WriteError();

        $this->assertInstanceOf(ExportException::class, new WriteError());
    }
}
