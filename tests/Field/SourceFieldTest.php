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

namespace Docalist\Data\Tests\Field;

use PHPUnit_Framework_TestCase;
use Docalist\Data\Field\SourceField;

/**
 * Teste la classe SourceField.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SourceFieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * Fournit des exemples de données à traiter et le résultat attendu.
     *
     * @return array[]
     */
    public function dataProvider()
    {
        $type = ['type' => 'crossref'];
        $url = ['url' => 'https://www.crossref.org/art/icle'];
        $value = ['value' => 'précisions'];

        $typeUrl = $type + $url;
        $typeValue = $type + $value;
        $urlValue = $url + $value;

        $typeUrlValue = $type + $url + $value;

        // data, format, result
        return [
            // Seulement type
            [
                $type,
                'type',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $type,
                'type-value',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $type,
                'link-type',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $type,
                'link-type-value',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $type,
                'link-type-tooltip',
                '<span class="crossref">CrossRef</span>'
            ],

            // Seulement url
            [
                $url,
                'type',
                '<span>crossref.org</span>'
            ],
            [
                $url,
                'type-value',
                '<span>crossref.org</span>'
            ],
            [
                $url,
                'link-type',
                '<a href="https://www.crossref.org/art/icle">crossref.org</a>'
            ],
            [
                $url,
                'link-type-value',
                '<a href="https://www.crossref.org/art/icle">crossref.org</a>'
            ],
            [
                $url,
                'link-type-tooltip',
                '<a href="https://www.crossref.org/art/icle">crossref.org</a>'
            ],

            // Seulement value
            [
                $value,
                'type',
                '<span>précisions</span>'
            ],
            [
                $value,
                'type-value',
                '<span>précisions</span>'
            ],
            [
                $value,
                'link-type',
                '<span>précisions</span>'
            ],
            [
                $value,
                'link-type-value',
                '<span>précisions</span>'
            ],
            [
                $value,
                'link-type-tooltip',
                '<span>précisions</span>'
            ],

            // Seulement type+url
            [
                $typeUrl,
                'type',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $typeUrl,
                'type-value',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $typeUrl,
                'link-type',
                '<a class="crossref" href="https://www.crossref.org/art/icle">CrossRef</a>'
            ],
            [
                $typeUrl,
                'link-type-value',
                '<a class="crossref" href="https://www.crossref.org/art/icle">CrossRef</a>'
            ],
            [
                $typeUrl,
                'link-type-tooltip',
                '<a class="crossref" href="https://www.crossref.org/art/icle">CrossRef</a>'
            ],

            // Seulement type+value
            [
                $typeValue,
                'type',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $typeValue,
                'type-value',
                '<span class="crossref">CrossRef (précisions)</span>'
            ],
            [
                $typeValue,
                'link-type',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $typeValue,
                'link-type-value',
                '<span class="crossref">CrossRef (précisions)</span>'
            ],
            [
                $typeValue,
                'link-type-tooltip',
                '<span class="crossref">CrossRef (précisions)</span>'
            ],

            // Seulement url+value
            [
                $urlValue,
                'type',
                '<span>précisions</span>'
            ],
            [
                $urlValue,
                'type-value',
                '<span>précisions</span>'
            ],
            [
                $urlValue,
                'link-type',
                '<a href="https://www.crossref.org/art/icle">précisions</a>'
            ],
            [
                $urlValue,
                'link-type-value',
                '<a href="https://www.crossref.org/art/icle">précisions</a>'
            ],
            [
                $urlValue,
                'link-type-tooltip',
                '<a href="https://www.crossref.org/art/icle">précisions</a>'
            ],

            // Seulement type+url+value
            [
                $typeUrlValue,
                'type',
                '<span class="crossref">CrossRef</span>'
            ],
            [
                $typeUrlValue,
                'type-value',
                '<span class="crossref">CrossRef (précisions)</span>'
            ],
            [
                $typeUrlValue,
                'link-type',
                '<a class="crossref" href="https://www.crossref.org/art/icle">CrossRef</a>'
            ],
            [
                $typeUrlValue,
                'link-type-value',
                '<a class="crossref" href="https://www.crossref.org/art/icle">CrossRef (précisions)</a>'
            ],
            [
                $typeUrlValue,
                'link-type-tooltip',
                '<a class="crossref" href="https://www.crossref.org/art/icle" title="précisions">CrossRef</a>'
            ],
        ];
    }

    /**
     * Teste getFormattedValue().
     *
     * @param array     $data       Données du champ source.
     * @param string    $format     Nom du format d'affichage à tester.
     * @param string    $result     Résultat attendu.
     *
     * @dataProvider dataProvider
     */
    public function testGetFormattedValue(array $data, string $format, string $result): void
    {
        $source = new SourceField($data);

        $message = sprintf('Format "%s" avec seulement %s', $format, implode(array_keys($data)));
        $this->assertSame($result, $source->getFormattedValue(['format' => $format]), $message);
    }
}
