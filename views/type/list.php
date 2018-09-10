<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Views;

use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Data\Settings\TypeSettings;

/**
 * Liste des types d'une base de données.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var int $dbindex L'index de la base.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s : types de notices', 'docalist-data'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Votre base de données contient les types de notices suivants :', 'docalist-data') ?>
    </p>

    <table class="widefat fixed">

    <thead>
        <tr>
            <th><?= __('Type', 'docalist-data') ?></th>
            <th><?= __('Description', 'docalist-data') ?></th>
        </tr>
    </thead>

    <?php
    $addType = $this->getUrl('TypeAdd', $dbindex);
    $nb = 0;
    foreach($database->types as $typeindex => $type) {
        /* @var TypeSettings $type */

        $edit = esc_url($this->getUrl('TypeEdit', $dbindex, $typeindex));
        $delete = esc_url($this->getUrl('TypeDelete', $dbindex, $typeindex));
        $listGrids = esc_url($this->getUrl('GridList', $dbindex, $typeindex));
        $recreate = esc_url($this->getUrl('TypeRecreate', $dbindex, $typeindex));

        $nb++;
    ?>

    <tr>
        <td class="column-title">
            <strong><a href="<?= $edit ?>"><?= $type->label() ?></a></strong> (<?= $type->name() ?>)
            <div class="row-actions">
                <span class="edit">
                    <a href="<?= $edit ?>">
                        <?= __('Paramètres', 'docalist-data') ?>
                    </a>
                </span>
                |
                <span class="fields">
                    <a href="<?= $listGrids ?>">
                        <?= __('Grilles et formulaires', 'docalist-data') ?>
                    </a>
                </span>
                |
                <span class="delete">
                    <a href="<?= $delete ?>">
                        <?= __('Supprimer ce type', 'docalist-data') ?>
                    </a>
                </span>
                |
                <span class="recreate">
                    <a href="<?= $recreate ?>">
                        <?= __('debug : recréer', 'docalist-data') ?>
                    </a>
                </span>
            </div>
        </td>

        <td><?= $type->description() ?></td>
    </tr>
    <?php
    } // end foreach

    if ($nb === 0) : ?>
        <tr>
            <td colspan="2">
                <em><?= __('Aucun type de notices dans cette base.', 'docalist-data') ?></em>
            </td>
        </tr>
    <?php endif; ?>

    </table>

    <p>
        <a href="<?= esc_url($addType) ?>" class="button button-primary">
            <?= __('Ajouter un type de notices...', 'docalist-data') ?>
        </a>
    </p>
</div>
