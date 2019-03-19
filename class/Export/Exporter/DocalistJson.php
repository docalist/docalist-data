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

namespace Docalist\Data\Export\Exporter;

use Docalist\Data\Export\Exporter\StandardExporter;
use Docalist\Data\Export\Converter\DocalistConverter;
use Docalist\Data\Operation\FilterEmptyArrayElements;
use Docalist\Data\Operation\FilterEmpty;
use Docalist\Data\Operation\SortArrayByKey;
use Docalist\Data\Export\Writer\JsonWriter;

/**
 * Export Docalist au format JSON.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJson extends StandardExporter
{
    public function __construct()
    {
        parent::__construct(new DocalistConverter(), new JsonWriter());
        $this->appendOperation(new FilterEmptyArrayElements());
        $this->appendOperation(new FilterEmpty());
        $this->appendOperation(new SortArrayByKey());
    }

    public static function getID(): string
    {
        return 'docalist-json';
    }

    public function getLabel(): string
    {
        return __('Docalist JSON', 'docalist-data');
    }

    public function getDescription(): string
    {
        return __(
            'Génère un <a href="https://fr.wikipedia.org/wiki/JavaScript_Object_Notation">fichier JSON</a>
            contenant les données des enregistrements Docalist en format natif.',
            'docalist-data'
        );
    }
}
