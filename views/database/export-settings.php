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

/**
 * Exporte (affiche) les paramètres d'une base.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var string $dbindex L'index de la base.
 * @var bool $pretty Code indenté ou pas.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s - exporter les paramètres de la base', 'docalist-data'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Recopiez le code ci-dessous pour faire une sauvegarde des paramètres de la base ou transférer les paramètres vers une autre base.', 'docalist-data') ?>
        <br />
        <?= __('Le code JSON affiché contient les paramètres de la base, les paramètres des types et les paramètres des grilles.', 'docalist-data') ?>
        <br />
        <?= __('Veillez à copier <b>la totalité du code</b> du, premier "{" au dernier "}", sinon cela ne fonctionnera pas.', 'docalist-data') ?>
        <?= __('Par exemple, cliquez dans la zone de texte et tapez Ctrl+A puis Ctrl+C.', 'docalist-data') ?>
    </p>

    <textarea class="code large-text" style="height: 80vh" readonly><?php
        echo json_encode($database, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | ($pretty ? JSON_PRETTY_PRINT : 0));
    ?></textarea>

    <p>
        <?php if ($pretty) :?>
            <a href="<?=remove_query_arg('pretty') ?>">Version compacte.</a>
        <?php else:?>
            <a href="<?=add_query_arg('pretty', 1) ?>">Version indentée.</a>
        <?php endif;?>
    </p>
</div>
