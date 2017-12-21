<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Views;

use Docalist\Databases\Pages\AdminDatabases;
use Docalist\Databases\Settings\DatabaseSettings;
use Docalist\Forms\Form;

/**
 * Importe les paramètres d'une base. Etape 1 :récupération du code json.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var string $dbindex L'index de la base.
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s - importer des paramètres', 'docalist-databases'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Collez le code contenant les paramètres à importer dans la zone de texte ci-dessous.', 'docalist-databases') ?>
    </p>

    <?php
        $form = new Form();
        $form->textarea('settings')
             ->setLabel(__('Paramètres à importer', 'docalist-databases'))
             ->setDescription(__("Collez le code que vous avez copié en utilisant l'option 'exporter paramètres'. Le code commence par { et se termine par }, veillez à tout inclure.", 'docalist-databases'))
             ->addClass('code large-text')
             ->setAttribute('style', 'height: 70vh');
        $form->submit(__('Importer...', 'docalist-databases'));

        $form->display();
    ?>
</div>
