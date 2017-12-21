<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Databases\Views;

use Docalist\Databases\Pages\AdminDatabases;
use Docalist\Databases\Settings\DatabaseSettings;
use Docalist\Schema\Schema;

/**
 * Choisit un type de notice à ajouter dans la base.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var int $dbindex L'index de la base.
 * @var Schema[] $types Liste des types disponibles.
 *
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
$back = $this->url('TypesList', $dbindex);
?>
<div class="wrap">
    <h1><?= sprintf(__('%s : ajouter un type de notice', 'docalist-databases'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Sélectionnez les types de notice à ajouter dans la base :', 'docalist-databases') ?>
    </p>

    <form method="POST" action="<?=$this->url('TypeAdd', $dbindex) ?>">
        <ul>
            <?php foreach($types as $type) : /** @var Schema $type */ ?>
                <li>
                    <h2>
                        <label>
                            <input type="checkbox" name="name[]" value="<?= $type->name() ?>" />
                            <?= $type->label() ?> (<?= $type->name() ?>)
                        </label>
                    </h2>
                    <p class="description"><?= $type->description() ?></p>
                </li>
            <?php endforeach ?>
        </ul>

        <p>
            <button type="submit" class="button-primary ">
                <?= __('Ajouter les types sélectionnés', 'docalist-databases') ?>
            </button>

            <a href="<?= esc_url($back) ?>" class="button">
                <?= __('Annuler', 'docalist-databases') ?>
            </a>
        </p>
    </form>
</div>
