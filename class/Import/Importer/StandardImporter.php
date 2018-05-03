<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Import\Importer;

use Docalist\Data\Import\Importer;
use Docalist\Data\Import\Reader;
use Docalist\Data\Import\Converter;
use Docalist\Pipeline\Pipeline;
use Docalist\Pipeline\StandardPipeline;

/**
 * Implémentation standard de l'interface Importer.
 *
 * L'implémentation est basée sur un {@link Pipeline pipeline} qui utilise un {@link Reader} et un {@link Converter}.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class StandardImporter implements Importer
{
    /**
     * Clé utilisée dans le pipeline pour le convertisseur.
     *
     * @var int
     */
    private const CONVERTER_KEY = 0;

    /**
     * Le Reader à utiliser pour charger les données à importer.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Le pipeline utilisé pour conversion les données en enregistrements Docalist.
     *
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * Initialise l'importeur.
     *
     * @param Reader    $reader     Le Reader à utiliser pour charger les données.
     * @param Converter $converter  Le convertisseur à utiliser pour convertir les données en enregistrements Docalist.
     */
    public function __construct(Reader $reader, Converter $converter)
    {
        $this->reader = $reader;
        $this->pipeline = new StandardPipeline();
        $this->pipeline->appendOperation($converter, self::CONVERTER_KEY);
    }

    /**
     * Ajoute une opération à exécuter avant la conversion.
     *
     * @param callable $operation L'opération à ajouter.
     */
    public function prependOperation(callable $operation): void
    {
        $this->pipeline->prependOperation($operation);
    }

    /**
     * Ajoute une opération à exécuter après la conversion.
     *
     * @param callable $operation L'opération à ajouter.
     */
    public function appendOperation(callable $operation): void
    {
        $this->pipeline->appendOperation($operation);
    }

    /**
     * Retourne le Reader utilisé.
     *
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * Retourne le convertisseur utilisé.
     *
     * @return Converter
     */
    public function getConverter(): Converter
    {
        return $this->pipeline->getOperation(self::CONVERTER_KEY);
    }

    public function getRecords(string $filename): Iterable
    {
        return $this->pipeline->process($this->getReader()->getRecords($filename));
    }
}
