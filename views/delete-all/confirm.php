<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Views;

use Docalist\Data\Database;
use Docalist\Data\Pages\ImportPage;

/**
 * Demande une confirmation à l'utilisateur.
 *
 * Si l'utilisateur clique "ok", la requête en cours est relancée avec en plus
 * le paramètre confirm=1.
 *
 * @var ImportPage $this
 * @var Database $database La base en cours.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

$count = $this->database->count();

$href = add_query_arg('confirm', '1');
$back = 'javascript:history.go(-1)';
?>

<div class="wrap">
    <h2><?= __('Vider la base', 'docalist-data') ?></h2>

    <div class="error">
        <?php if ($count ===0): ?>
            <h3><?= __('La base est vide', 'docalist-data') ?></h3>
            <p><?= __("Il n'y a aucune notice à supprimer", 'docalist-data') ?></p>
            <p>
                <a href="<?= $back ?>" class="button-primary">
                    <?= __('Ok', 'docalist-data') ?>
                </a>
            </p>

        <?php else: ?>
            <h3><?= __('Attention', 'docalist-data') ?></h3>

            <p>
                <?= sprintf(__('Vous allez supprimer définitivement <b>%d notices</b>.', 'docalist-data'), $count) ?>
            </p>

            <p>
                <?= __('Toutes les données liées à ces notices seront également supprimées :', 'docalist-data') ?>
            </p>

            <ul class="ul-square">
                <li><?= __('meta-données des notices,', 'docalist-data') ?></li>
                <li><?= __('termes de taxonomies éventuels (mots-clés, catégories, etc.),', 'docalist-data') ?></li>
                <li><?= __('révisions et sauvegardes automatiques des notices,', 'docalist-data') ?></li>
                <li><?= __('commentaires sur les notices et meta-données liées à ces commentaires.', 'docalist-data') ?></li>
            </ul>

            <p>
                <i><?= __("Remarque :", 'docalist-data') ?></i>
                <?= __("Si cette base contient des notices parent de notices situées dans d'autres bases, celles-ci n'auront plus de parent.", 'docalist-data') ?>
            </p>

            <p>
                <b><?= __("La suppression est définitive. Voulez-vous continuer ?", 'docalist-data') ?></b>
            </p>

            <p>
                <a href="<?= $href ?>" class="button-primary">
                    <?= __('Vider la base', 'docalist-data') ?>
                </a>

                <a href="<?= $back ?>" class="button">
                    <?= __('Annuler', 'docalist-data') ?>
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>
