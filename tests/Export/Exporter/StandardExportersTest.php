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
use Docalist\Data\Export\Exporter\StandardExporters;
use Docalist\Data\Export\Exporter\DocalistJson;
use Docalist\Data\Export\Exporter\DocalistJsonPretty;
use Docalist\Data\Export\Exporter\DocalistXml;
use Docalist\Data\Export\Exporter\DocalistXmlPretty;

/**
 * Teste la liste des formats disponibles.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class StandardExportersTest extends PHPUnit_Framework_TestCase
{
    /**
     * Teste la méthode getList()
     */
    public function NUtestGetList()
    {
        $list = StandardExporters::getList();

        $this->assertSame(4, count($list));
        $this->assertTrue(isset($list[DocalistJson::getID()]));
        $this->assertTrue(isset($list[DocalistJsonPretty::getID()]));
        $this->assertTrue(isset($list[DocalistXml::getID()]));
        $this->assertTrue(isset($list[DocalistXmlPretty::getID()]));
    }
}
