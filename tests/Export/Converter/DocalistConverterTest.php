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

use PHPUnit_Framework_TestCase;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Export\Converter;
use Docalist\Data\Record;
use Docalist\Type\Entity;
use Docalist\Data\Entity\ContentEntity;

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
    public function testInvoke()
    {
        // Crée le convertisseur
        $convert = new DocalistConverter();

        // Vérifie que le convertisseur a été marqué avec l'interface "Converter"
        $this->assertInstanceOf(Converter::class, $convert);

        // Vérifie que le convertisseur est un callable (redondant avec le test précédent mais ne nuit pas)
        $this->assertTrue(is_callable($convert));

        // Vérifie que le convertisseur retourne bien tableau vide si on lui passe un enregsitrement vide
        $record = new Record();
        $this->assertSame([], $convert($record));

        // Vérifie que le convertisseur nous retourne bien les données des enregsitrements
        $data = ['posttype' => 'record', 'posttitle' => 'titre'];
        $record = new Record($data);
        $this->assertSame($data, $convert($record));
    }

    public function testSupports()
    {
        $converter = new DocalistConverter();
        $this->assertFalse($converter->supports('XXX'));
        $this->assertFalse($converter->supports(Entity::class));
        $this->assertTrue($converter->supports(Record::class));
        $this->assertTrue($converter->supports(ContentEntity::class));
    }
}
