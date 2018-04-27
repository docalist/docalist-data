<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Exporter;

use Docalist\Data\Export\Exporter;
use Docalist\Data\Export\Converter;
use Docalist\Data\Export\Writer;
use Docalist\Pipeline\Pipeline;
use Docalist\Pipeline\StandardPipeline;
use Docalist\Data\Record;

/**
 * Implémentation standard de l'interface Exporter.
 *
 * Un exporteur standard utilise {@link Converter un convertisseur} qui effectue la conversion des
 * {@link Record enregistrements Docalist} en données exportables et {@link Writer un Writer} qui se charge de
 * générer le fichier d'export.
 *
 * En interne, la conversion est gérée par un pipeline de données. Des traitements supplémentaires peuvent être
 * ajoutés avant ou après la conversion en utilisant les méthodes prependOperation() et appendOperation().
 *
 * <code>
 * <-- input -->    <------------------------ pipeline ----------------------->    <--- writer --->    <-- output -->
 *
 *       Records -> [operations] -> data -> [Converter] -> data -> [operations] -> data -> [Writer] -> file.
 *                       ^                                              ^
 *                prependOperation()                              appendOperation()
 * </code>
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class StandardExporter implements Exporter
{
    /**
     * Clé utilisée dans le pipeline pour le convertisseur.
     *
     * @var int
     */
    private const CONVERTER_KEY = 0;

    /**
     * Le pipeline utilisé pour la conversion des enregistrements.
     *
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * Le Writer à utiliser pour générer le fichier d'export.
     *
     * @var Writer
     */
    protected $writer;

    /**
     * Initialise l'exporteur.
     *
     * @param Converter $converter  Le convertisseur à utiliser pour convertir les enregistrements Docalist.
     * @param Writer    $writer     Le Writer à utiliser pour générer le fichier d'export.
     */
    public function __construct(Converter $converter, Writer $writer)
    {
        $this->pipeline = new StandardPipeline();
        $this->pipeline->appendOperation($converter, self::CONVERTER_KEY);

        $this->writer = $writer;
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
     * Retourne le convertisseur utilisé.
     *
     * @return Converter
     */
    public function getConverter(): Converter
    {
        return $this->pipeline->getOperation(self::CONVERTER_KEY);
    }

    /**
     * Retourne le Writer utilisé.
     *
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }

    public function supports(string $className): bool
    {
        return $this->getConverter()->supports($className);
    }

    public function getContentType(): string
    {
        return $this->getWriter()->getContentType();
    }

    public function isBinaryContent(): bool
    {
        return $this->getWriter()->isBinaryContent();
    }

    public function suggestFilename(): string
    {
        return static::getID() . '-' . $this->getWriter()->suggestFilename();
    }

    public function export(Iterable $records)
    {
        $this->getWriter()->export($this->pipeline->process($records));
    }
}
