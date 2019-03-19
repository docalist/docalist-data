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

use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Data\Settings\TypeSettings;

/**
 * Affiche la liste des bases de données existantes.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings[] $databases Liste des bases de données.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<style>
div.dbdesc{
    white-space: pre-wrap;
    max-height: 10em;
    overflow-y: auto;
}
</style>
<div class="wrap">
    <h1><?= __('Gestion des bases Docalist', 'docalist-data') ?></h1>

    <p class="description">
        <?= __('Voici la liste de vos bases Docalist :', 'docalist-data') ?>
    </p>

    <table class="widefat fixed">

    <thead>
        <tr>
            <th><?= __('Nom de la base', 'docalist-data') ?></th>
            <th><?= __('Page d\'accueil', 'docalist-data') ?></th>
            <th><?= __('Types de notices', 'docalist-data') ?></th>
            <th><?= __('Nombre de notices', 'docalist-data') ?></th>
            <th><?= __('Description', 'docalist-data') ?></th>
        </tr>
    </thead>

    <?php
    $nb = 0;
    foreach($databases as $dbindex => $database) { /* @var DatabaseSettings $database */
        $edit = esc_url($this->getUrl('DatabaseEdit', $dbindex));
        $delete = esc_url($this->getUrl('DatabaseDelete', $dbindex));
        $listTypes = esc_url($this->getUrl('TypesList', $dbindex));
        $exportSettings = esc_url($this->getUrl('DatabaseExportSettings', $dbindex));
        $importSettings = esc_url($this->getUrl('DatabaseImportSettings', $dbindex));

        $count = wp_count_posts($database->postType())->publish;
        $listRefs = esc_url(admin_url('edit.php?post_type=' . $database->postType()));
        $nb++; ?>

        <tr>
            <td class="column-title">
                <strong>
                    <a href="<?= $edit ?>"><?= $database->label() ?></a>
                </strong>
                <div class="row-actions">
                    <span class="edit">
                        <a href="<?= $edit ?>">
                            <?= __('Paramètres', 'docalist-data') ?>
                        </a>
                    </span>
                    |
                    <span class="list-types">
                        <a href="<?= $listTypes ?>">
                            <?= __('Types de notices', 'docalist-data') ?>
                        </a>
                    </span>
                    |
                    <span class="delete">
                        <a href="<?= $delete ?>">
                            <?= __('Supprimer', 'docalist-data') ?>
                        </a>
                    </span>
                    <br />
                    <span class="export-settings">
                        <a href="<?= $exportSettings ?>">
                            <?= __('Exporter paramètres', 'docalist-data') ?>
                        </a>
                    </span>
                    |
                    <span class="import-settings">
                        <a href="<?= $importSettings ?>">
                            <?= __('Importer paramètres', 'docalist-data') ?>
                        </a>
                    </span>
                </div>
            </td>

            <td><a href="<?= $database->url() ?>"><?= $database->slug() ?></a></td>
            <td>
                <?php if (0 === count($database->types)): ?>
                    <a href="<?= esc_url($this->getUrl('TypeAdd', $dbindex)) ?>">
                        <?= __('Ajouter un type...', 'docalist-data') ?>
                    </a>
                <?php else: ?>
                    <?php foreach ($database->types as $typeindex => $type): /* @var TypeSettings $type */ ?>
                        <a href="<?= esc_url($this->getUrl('GridList', $dbindex, $typeindex)) ?>">
                            <?= $type->label() ?>
                        </a>
                        <br />
                    <?php endforeach ?>
                <?php endif ?>

            </td>
            <td><a href="<?= $listRefs ?>"><?= $count ?></a></td>
            <td><div class="dbdesc"><?= $database->description() ?></div></td>
        </tr>
        <?php
    } // end foreach

    if ($nb === 0) : ?>
        <tr>
            <td colspan="4">
                <em><?= __('Aucune base définie.', 'docalist-data') ?></em>
            </td>
        </tr><?php
    endif; ?>

    </table>

    <p>
        <a href="<?= esc_url($this->getUrl('DatabaseAdd')) ?>" class="button button-primary">
            <?= __('Créer une base...', 'docalist-data') ?>
        </a>
    </p>
</div>
