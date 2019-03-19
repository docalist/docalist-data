<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Views;

use Docalist\Data\Export\ExportService;
use Docalist\Data\Export\Exporter;

/**
 * Affiche le formulaire d'export.
 *
 * @var ExportService   $this
 * @var array[]         $exportersInfo  Informations sur les exporteurs dispos, cf. ExportService::getExportersInfo()
 * @var int             $count          Nombre total de notices à exporter.
 * @var int             $max            Nombre maximum de notices qui peuvent être exportées (limite de l'export).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
$this->setTitle(__("Générer un fichier d'export", 'docalist-data'));

// Helper : met en minuscule la première lettre des éléments passés en paramètre.
$lcfirst = function (array $list): array {
    return array_map('lcfirst', $list);
};

// Helper : ajoute des guillemets autour des éléments.
$quote = function (array $list): array {
    return array_map(function (string $item): string {
        return '"' . $item . '"';
    }, $list);
};

// Helper : génère une liste formattée sous la forme "item1, item2 et item3".
$list = function (array $list): string {
    if (count($list) <= 1) {
        return (string) reset($list);
    }

    $last = array_pop($list);
    return implode(', ', $list) . __(' et ', 'docalist-data') . $last;
};

// Helper : retourne "1 notice" ou "x notices" en fonction du count passé en paramètre
$record = function (int $count): string {
    return sprintf(_n('%d notice', "%d notices", $count, 'docalist-data'), $count);
};

// Indique qu'il y a une limite sur le nombre de notices qu'on peut exporter en une seule fois
if ($count <= $max) {
    $message = sprintf(
        __("Choisissez le format à utiliser pour exporter %s :", 'docalist-data'),
        sprintf(_n('la notice sélectionnée', "les %d notices sélectionnées", $count, 'docalist-data'), $count)
    );
} else {
    $message = sprintf(
        __(
            "Votre sélection contient <b>%s</b> mais notre module d'export ne permet pas d'exporter plus de %s en
            une seule étape. Nous vous conseillons <a href=\"%s\" title=\"%s\">d'affiner votre sélection</a> pour
            rester en dessous de cette limite mais vous pouvez également continuer et exporter les %d premières
            notices de votre sélection en cliquant sur l'un des formats proposés ci-dessous :",
            'docalist-data'
        ),
        $record($count),
        $record($max),
        'javascript:history.back()',
        esc_attr(__('Retour à la page précédente', 'docalist-data')),
        $max
    ); ?>
    <?php
} ?>

<p class="export-intro"><?= $message ?></p>

<ol class="export-formats"><?php
    // Détermine l'url en cours
    $baseUrl = $_SERVER['REQUEST_URI'];
    $baseUrl .= (false === strpos($baseUrl, '?')) ? '?' : '&';

    // Génère une entrée pour chaque exporteur dispo
    foreach ($exportersInfo as $exporter) {
        // $exporter contient :
        // - 'ID' : ID de l'exporteur,
        // - 'class' : Nom complet de la classe PHP de l'exporteur,
        // - 'label' : Libellé de l'exporteur,
        // - 'description' : Description de l'exporteur,
        // - 'supported' : Liste des types d'enregistrements supportés,
        // - 'unsupported' : Liste des types d'enregistrements non supportés,
        // - 'count' : Nombre total de notices qui pourront être exportées.

        $id = $exporter['ID'];
        $label = $exporter['label'] ?: $id;
        $description = $exporter['description'];
        $url = $baseUrl . '_exporter=' . rawurlencode($id);
        $unsupported = '';
        if (! empty($exporter['unsupported'])) {
            if ($exporter['count'] <= $max) {
                $unsupported = sprintf(
                    __(
                        'Remarque : votre export contiendra seulement %s car les contenus de type %s
                        ne peuvent pas être exportés dans ce format.',
                        'docalist-data'
                    ),
                    $record($exporter['count']),
                    $list($quote($lcfirst($exporter['unsupported'])))
                );
            } else {
                $unsupported = sprintf(
                    __(
                        'Remarque : les contenus de type %s ne peuvent pas être exportés dans ce format.',
                        'docalist-data'
                    ),
                    $list($quote($lcfirst($exporter['unsupported'])))
                );

            }
        } ?>

        <li class="export-<?= esc_attr($id) ?>">
            <h3 class="export-format">
                <a href="<?= esc_attr($url) ?>"><?= $label ?></a>
            </h3><?php

            if (! empty($description)) { ?>
                <p class="export-format-description"><?= $description ?></p><?php
            }

            if (! empty($unsupported)) { ?>
                <p class="export-format-unsupported-types"><?= $unsupported ?></p><?php
            } ?>
        </li><?php
    } ?>
</ol>
