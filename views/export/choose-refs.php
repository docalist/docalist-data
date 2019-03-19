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
use Docalist\Forms\Container;
use Docalist\Data\Pages\DatabaseTools;

/**
 * Export de notices : choix des notices à exporter.
 *
 * @var DatabaseTools $this
 * @var Database $database Base de données en cours.
 * @var array $exporter Le nom de code de l'exporteur en cours.
 * @var string $error Optionnel, erreur à afficher.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

// TODO : utiliser get_search_form() ?
$form = new Container();
/*
$form->attribute('class', 'form-horizontal')
->attribute('id', 'advanced-search');
*/
$form->input('q')
->setLabel('Equation :')
->setAttribute('class', 'input-block-level');
/*
$form->input('topic.filter')
->label('Mots-clés :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:topic.suggest')
->attribute('data-multiple', true);

$form->input('title')
->label('Mots du titre :')
->attribute('class', 'input-block-level');

$form->input('author.filter')
->label('Auteur :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:author.suggest')
->attribute('data-multiple', true);

$form->input('corporation.filter')
->label('Organisme :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:corporation.suggest');

$form->input('journal.filter')
->label('Revue :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:journal.suggest');

$form->input('date')
->label('Date :');

$form->checklist('type.filter')
->noBrackets(true)
->label('Type :')
->options(array('Article', 'Livre', 'Mémoire', 'Document audiovisuel', 'Cd-rom'))
->attribute('class', 'inline');

$form->tag('div.form-actions', "
        <button type='submit' class='btn btn-primary pull-left'>Rechercher...</button>
        <a class='btn btn-mini pull-right' href='$reset'>Nouvelle recherche</a>
        ");

$form->bind($url);
$form->render('bootstrap');
*/
?>
<div class="wrap">
    <h2><?= sprintf(__('Export %s', 'docalist-data'), $database->settings()->label) ?></h2>

    <p class="description">
        <?= __("Choisissez les notices à exporter.", 'docalist-data') ?>
    </p>

    <?php if (isset($error)): ?>
    <div class="error">
        <p><?=$error ?>
    </div>

    <?php endif; ?>

    <form action="" method="post">
        <?php
            $form->bind($_REQUEST)->display('wordpress');
        ?>

        <div class="submit buttons">
            <button type="submit" class="run-export">
                <?=__("Lancer l'export...", 'docalist-data') ?>
            </button>
        </div>
        <?php if(!empty($exporter)): ?>
            <input type="hidden" name="exporter" value="<?= $exporter ?>" />
        <?php endif; ?>
    </form>
</div>
