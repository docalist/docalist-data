<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Databases\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Export;

use InvalidArgumentException;
use Docalist\Databases\Reference\ReferenceIterator;
use Docalist\Search\SearchRequest;

/**
 * Un format d'export composé d'un converter et d'un exporter.
 */
class Format
{
    /**
     * Le nom du format.
     *
     * @var string
     */
    protected $name;

    /**
     * Les paramètres du format.
     *
     * @var array
     */
    protected $format;

    /**
     * Le converter de ce format, créé à la demande par converter().
     *
     * @var Converter
     */
    protected $converter;

    /**
     * L'eexporter de ce format, créé à la demande par exporter().
     *
     * @var Exporter
     */
    protected $exporter;

    /**
     * Initialise l'objet.
     *
     * @param string    $name   Le nom du format.
     * @param array     $format Les paramètres de l'objet.
     *
     * - label : optionnel, string, libellé du format.
     * - description : optionnel, string description du format.
     * - converter : obligatoire, string, nom de classe complet du converter.
     * - converter-settings : optionnel, array, paramètres du converter.
     * - exporter : obligatoire, string, nom de classe complet de l'exporter.
     * - exporter-settings : optionnel, array, paramètres de l'exporter.
     */
    public function __construct($name, array $format)
    {
        // Vérifie que les clés obligatoires sont définies dans les options
        foreach(['converter', 'exporter'] as $key) {
            if (!isset($format[$key])) {
                $msg = sprintf(__('La clé %s doit être définie pour le le format %s.', 'docalist-databases'), $key, $name);
                throw new InvalidArgumentException($msg);
            }

        }

        $this->name = $name;
        $this->format = $format;
    }

    /**
     * Retourne le nom du format.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Retourne le libellé du format.
     *
     * @return string
     */
    public function getLabel()
    {
        if (isset($this->format['label'])) {
            return $this->format['label'];
        }

        return $this->name;
    }

    /**
     * Retourne la description du format.
     *
     * @return string
     */
    public function getDescription()
    {
        if (isset($this->format['description'])) {
            return $this->format['description'];
        }

        return $this->converter()->getDescription() . '<br />' . $this->exporter()->getDescription();
    }

    /**
     * Crée et retourne le converter de ce format.
     *
     * @return Converter
     */
    public function converter()
    {
        if (! isset($this->converter)) {
            $converter = $this->format['converter'];
            $settings = isset($this->format['converter-settings']) ? $this->format['converter-settings'] : [];
            $this->converter = new $converter($settings);
        }

        return $this->converter;
    }

    /**
     * Crée et retourne l'exporter de ce format.
     *
     * @return Exporter
     */
    public function exporter()
    {
        if (! isset($this->exporter)) {
            $exporter = $this->format['exporter'];
            $settings = isset($this->format['exporter-settings']) ? $this->format['exporter-settings'] : [];
            $this->exporter = new $exporter($this->converter(), $settings);
        }

        return $this->exporter;
    }

    /**
     * Exporte le lot de notices passé en paramètre.
     *
     * @param SearchRequest $request La requête contenant les notices à exporter.
     */
    public function export(SearchRequest $request, $disposition = 'inline', $limit = null)
    {
        // Crée l'itérateur
        $iterator = new ReferenceIterator($request, $limit);

        // Crée l'exporteur
        $exporter = $this->exporter();

        // Génère les entêtes http
        header('Content-Type: ' . $exporter->contentType());
        header('Content-Disposition: ' . $exporter->contentDisposition($disposition));
        header('X-Content-Type-Options: nosniff');

        // Lance l'export
        $this->exporter()->export($iterator);
    }
}
