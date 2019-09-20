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
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'un type.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var int $dbindex L'index de la base.
 * @var TypeSettings $type Le type à éditer.
 * @var int $typeindex L'index du type.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s - paramètres du type "%s"', 'docalist-data'), $database->label(), $type->name()) ?></h1>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres du type :', 'docalist-data') ?>
    </p>

    <?php
        $form = new Form();

        $form->input('label')
             ->addClass('regular-text');
        $form->textarea('description')
             ->setAttribute('rows', '2')
             ->addClass('large-text');
        $form->submit(__('Enregistrer les modifications', 'docalist-data'));

        $form->bind($type);
        $form->display();
    ?>
</div>
