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

use Docalist\Data\Pages\EditReference;
use Docalist\Data\Database;

/**
 * Permet à l'utilisateur de choisir le type de notice à créer.
 *
 * @var EditReference $this
 * @var Database $database Base de données en cours.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<div class="wrap">
    <?php
        $title = sprintf(__('%1$s » %2$s', 'docalist-search'),
            $database->settings()->label(),
            get_post_type_object($database->postType())->labels->add_new_item
        );
    ?>
    <h1><?= $title ?></h1>

    <p class="description">
        <?= __("Choisissez le type de notice à créer.", 'docalist-data') ?>
    </p>
    <table class="widefat">
        <?php $nb = 0 ?>
        <?php foreach($database->settings()->types as $type): ?>
            <tr class="<?= ++$nb % 2 ? 'alternate' : '' ?>">
                <td class="row-title">
                    <a href="<?= esc_url(add_query_arg('ref_type', $type->name())) ?>">
                        <?= $type->label() ?>
                    </a>
                </td>
                <td class="desc">
                    <?= $type->description() ?>
                </td>
            </tr>
        <?php endforeach ?>

        <?php if ($nb === 0): ?>
            <tr>
                <td class="desc" colspan="2">
                    <?= __('Aucun type disponible dans cette base.', 'docalist-data') ?>
                </td>
            </tr>
        <?php endif ?>
    </table>
</div>
