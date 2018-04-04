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

use Docalist\Pipeline\StandardPipeline;
use Docalist\Data\Export\Exporter;
use Docalist\Data\Export\Writer;

/**
 * Classe de base pour les exporteurs standard.
 *
 * Un exporteur standard utilise un pipeline de données pour convertir les enregistrements à exporter et un
 * Writer pour générer le fichier.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class StandardExporter extends StandardPipeline implements Exporter
{
    /**
     * Le Writer à utiliser pour générer le fichier d'export.
     *
     * @var Writer
     */
    protected $writer;

    /**
     * Initialise l'exporteur.
     *
     * @param callable[]    $operations La liste des opérations qui composent le pipeline d'export.
     * @param Writer        $writer     Le Writer à utiliser pour générer le fichier d'export.
     */
    public function __construct(array $operations, Writer $writer)
    {
        parent::__construct($operations);
        $this->setWriter($writer);
    }

    /**
     * Modifie le Writer utilisé.
     *
     * @param Writer $writer
     *
     * @return self
     */
    public function setWriter(Writer $writer): self
    {
        $this->writer = $writer;

        return $this;
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
        $this->getWriter()->export($this->process($records));
    }
}
