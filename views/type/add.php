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
$back = $this->getUrl('TypesList', $dbindex);
?>
<div class="wrap">
    <h1><?= sprintf(__('%s : ajouter un type de notice', 'docalist-data'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Sélectionnez les types de notice à ajouter dans la base :', 'docalist-data') ?>
    </p>

    <form method="POST" action="<?=$this->getUrl('TypeAdd', $dbindex) ?>">
        <ul>
            <?php foreach($types as $type) : /* @var Schema $type */ ?>
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
                <?= __('Ajouter les types sélectionnés', 'docalist-data') ?>
            </button>

            <a href="<?= esc_url($back) ?>" class="button">
                <?= __('Annuler', 'docalist-data') ?>
            </a>
        </p>
    </form>
</div>
