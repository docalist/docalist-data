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

namespace Docalist\Data\Views;

use Docalist\Data\Database;

/**
 * Cette vue est affichée lorsqu'on lance l'import d'un ou plusieurs fichiers
 * dans une base de données.
 *
 * Lorsqu'elle est exécutée, cette vue installe des filtres qui doivent être
 * appellés au bon moment par les importeurs.
 *
 * Six filtres sont installés :
 * - docalist_databases_before_import : début de l'import des fichier. Affiche le
 *   début de la page (début de la div.wrap, titre h2, p.description).
 *
 * - docalist_databases_import_start : début de l'import d'un fichier donné.
 *   Affiche un titre h3 avec le nom du type en cours et ouvre un <ul>.
 *
 * - docalist_databases_import_progress : affiche l'avancement de l'import (par
 *   exemple le nombre de notices importées jusque là).
 *   Affiche le début d'un <li> indiquant le nombre de notices chargées.
 *
 * - docalist_databases_import_error : affiche une erreur.
 *
 * - docalist_databases_import_done : fin de l'import d'un fichier donné. Affiche
 *   le nombre de notices chargées pour ce fichier.
 *
 * - docalist_databases_after_import : fin de l'import des fichiers choisis.
 *
 * Remarque : cette vue ne prend aucun paramètre, ils sont passés directement
 * aux callbacks des filtres installés.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

$startTime = microtime(true);

/**
 * before_import : début de l'import.
 *
 * Affiche le début de la page (début de la div.wrap, titre h2, p.description).
 *
 * @param array $files Un tableau contenant la liste des fichiers à importer
 * sous la forme $path => $importer.
 *
 * @param Database $database La base de données destination
 */
add_action('docalist_databases_before_import', function(array $files, Database $database, array $options) { ?>
    <?php
        if ($options['simulate'] ?? false) {
            $h2 = __("Simulation de l'import de fichiers", 'docalist-data');
            $desc = __(
                "Vous avez lancé une simulation d'import de fichiers dans la base <strong>%s</strong>.",
                 'docalist-data'
             );
        } else {
            $h2 = __("Import de fichiers", 'docalist-data');
            $desc = __(
                'Vous avez lancé un import de fichiers dans la base <strong>%s</strong>.',
                 'docalist-data'
             );
        }
    ?>
    <div class="wrap">
        <h2><?= $h2 ?></h2>

        <p class="description"><?php
            //@formatter:off
            printf(
                $desc,
                $database->getSettings()->label
            );
            echo '<br />';
            _e('La page affichera des informations supplémentaires au fur
                et à mesure de l\'avancement. Veuillez patienter.', 'docalist-data'
            );
            // @formatter:on
            ?>
        </p>

        <?php
        flush();
}, 10, 3); ?>

<?php
/**
 * docalist_databases_import_start : début de l'import d'un fichier donné.
 *
 * Affiche un titre h3 et ouvre un <ul>.
 *
 * @param string $file le path du fichier qui va être importé.
 */
add_action('docalist_databases_import_start', function($file, $options) { ?>
    <?php
        if ($options['simulate'] ?? false) {
            $h3 = __('Test du fichier %s', 'docalist-data');
        } else {
            $h3 = __('Import du fichier %s', 'docalist-data');
        }
    ?>

    <h3><?= sprintf($h3, basename($file)) ?></h3>
    <ul class="ul-square">
    <?php
    flush();
}, 10, 2);
?>

<?php
/**
 * docalist_databases_import_progress : affiche un message de progression.
 *
 * @param string $message Message à afficher.
 */
add_action('docalist_databases_import_progress', function($message) {
    echo '<li>', $message, '</li>';
    flush();
}, 10, 1);
?>

<?php
/**
 * docalist_databases_import_error : signale une erreur
 *
 * @param string $error Message d'erreur à afficher.
 */
add_action('docalist_databases_import_error', function($error) {
    echo '<li style="color: red; font-weight: bold;">', $error, '</li>';
    flush();
}, 10, 1);
?>

<?php
/**
 * docalist_databases_import_done : fin de l'import d'un fichier donné.
 *
 * Ferme le <ul> ouvert par docalist_databases_import_start.
 *
 * @param string $file le path du fichier qui a été importé.
 */
add_action('docalist_databases_import_done', function($file, $options) { ?>
    </ul>
    <?php
    flush();
}, 10, 2);
?>

<?php
/**
 * docalist_databases_after_import : fin de l'import.
 *
 * @param array $files Un tableau contenant la liste des fichiers importés
 * sous la forme $path => $importer.
 *
 * @param Database $database La base de données destination
 */
add_action('docalist_databases_after_import', function(array $files, Database $database, array $options) use ($startTime) { ?>
        <h3><?= __('Terminé !', 'docalist-data') ?></h3>
    </div>
    <?php
    if ($options['simulate'] ?? false) {
        $msg = _n(
            'La simulation d\'import est terminée : le fichier %2$s a été testé.',
            'La simulation d\'import est terminée : %d fichiers ont été testés (%2$s).',
            count($files), 'docalist-data');
    } else {
        $msg = _n(
            'L\'import est terminé : le fichier %2$s a été importé dans %3$s.',
            'L\'import est terminé : %d fichiers ont été importés dans %3$s (%2$s).',
            count($files), 'docalist-data');
    }

    printf("<p>$msg</p>",
        count($files),
        implode(', ', array_map('basename', array_keys($files))),
        $database->getSettings()->label
    );

    $msg = __('Temps écoulé : %.2f secondes.', 'docalist-data');
    printf("<p>$msg</p>", (microtime(true) - $startTime));

    flush();
}, 10, 3);
