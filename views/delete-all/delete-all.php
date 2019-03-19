<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Views;

use Docalist\Data\Database;
use Docalist\Data\Pages\DatabaseTools;

/**
 * Cette vue est affichée lorsqu'on lance l'option "vider la base".
 *
 * Lorsqu'elle est exécutée, cette vue installe des filtres qui seront
 * appellés au bon moment lors de la suppression des notices.
 *
 * Remarque : cette vue ne prend aucun paramètre, ils sont passés directement
 * aux callbacks des filtres installés.
 *
 * @var DatabaseTools $this
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

$startTime = microtime(true);

/**
 * before_deleteall : début de la suppression.
 *
 * Affiche le début de la page (début de la div.wrap, titre h2, p.description).
 *
 * @param Database $database La base de données en cours.
 * @param int $count Le nombre de notices à supprimer.
 */
add_action('docalist_databases_deleteall_start', function(Database $database, $count) { ?>
    <div class="wrap">
        <h2><?= __("Vider la base", 'docalist-data') ?></h2>

        <p class="description"><?php
            printf(
                __("Il y a %d notices à supprimer", 'docalist-data'),
                $count
            );
            ?>
        </p>

        <ul class="ul-square">

        <?php
        flush();
}, 10, 2); ?>

<?php
/**
 * docalist_databases_deleteall_progress : affiche un message de progression de la
 * suppression.
 *
 * @param string $message Message à afficher.
 */
add_action('docalist_databases_deleteall_progress', function($message) {
    echo '<li>', $message, '</li>';
    flush();
}, 10, 1);
?>

<?php
/**
 * docalist_databases_deleteall_done : fin de la suppression.
 *
 * Ferme le <ul> ouvert par docalist_databases_deleteall_start.
 *
 * @param Database $database La base de données en cours.
 * @param int $count Le nombre de notices supprimées.
 */
add_action('docalist_databases_deleteall_done', function(Database $database, $count) use ($startTime) { ?>
        </ul> <?php
            $msg = __('%d notices supprimées. Temps écoulé : %.2f secondes', 'docalist-data');
            printf("<p>$msg</p>", $count, (microtime(true) - $startTime)); ?>
    </div> <?php
    flush();
}, 10, 2);
?>
