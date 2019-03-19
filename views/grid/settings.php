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

use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Data\Settings\TypeSettings;
use Docalist\Schema\Schema;
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'une grille.
 *
 * @var AdminDatabases      $this
 * @var DatabaseSettings    $database   La base à éditer.
 * @var int                 $dbindex    L'index de la base.
 * @var TypeSettings        $type       Le type à éditer.
 * @var int                 $typeindex  L'index du type.
 * @var Schema              $grid       La grille à éditer.
 * @var string              $gridname   L'index de la grille.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s - %s - %s - paramètres', 'docalist-data'), $database->label(), $type->name(), $grid->label()) ?></h1>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres généraux de la grille.', 'docalist-data') ?>
    </p>

    <?php
        $form = new Form();

        $form->input('name')
             ->addClass('regular-text')
             ->setAttribute('disabled')
             ->setLabel(__('Nom', 'docalist-data'))
             ->setDescription(__('Nom interne de la grille (non modifiable).', 'docalist-data'));
        $form->input('label')
             ->addClass('regular-text')
             ->setLabel(__('Libellé', 'docalist-data'))
             ->setDescription(__('Libellé utilisé pour désigner cette grille.', 'docalist-data'));
        $form->textarea('description')
             ->setAttribute('rows', 2)
             ->addClass('large-text')
             ->setLabel(__('Description', 'docalist-data'))
             ->setDescription(__('Description libre.', 'docalist-data'));
        $form->submit(__('Enregistrer les modifications', 'docalist-data'));

        $form->bind($grid)->display();
    ?>
</div>
