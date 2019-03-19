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

namespace Docalist\Data\Tests\Export\Exporter;

use Docalist\Data\Tests\Export\Exporter\DocalistXmlTest;
use Docalist\Data\Export\Exporter\DocalistXmlPretty;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Export\Writer\XmlWriter;

/**
 * Teste l'export Docalist au format XML Pretty.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistXmlPrettyTest extends DocalistXmlTest
{
    const EXPORTER = DocalistXmlPretty::class;
    const EXPECTED_ID = 'docalist-xml-pretty';
    const EXPECTED_CONVERTER = DocalistConverter::class;
    const EXPECTED_WRITER = XmlWriter::class;
    const EXPECTED_FILENAME = 'docalist-xml-pretty-export.xml';

    protected function getExpectedExport()
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<records>',
                // Enregistrement 1
            '    <record>',
            '        <content>',
            '            <item>',
            '                <type>content</type>',
            '                <value>content</value>',
            '            </item>',
            '        </content>',
            '        <posttitle>Welcome</posttitle>',
            '        <slug>test</slug>',
            '    </record>',

                // L'enregistrement vide a été supprimé

                // Enregistrement 2
            '    <record>',
            '        <slug>test2</slug>',
            '    </record>',
            '</records>',
            ''
        ];

        return implode("\n", $lines);
    }
}
