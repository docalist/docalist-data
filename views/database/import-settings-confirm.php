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
use Docalist\Forms\Form;

/**
 * Importe les paramètres d'une base. Etape 2 : choix des types.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base en cours.
 * @var string $dbindex L'index de la base.
 * @var array $settings Les paramètres à importer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

$settings = $settings; // évite warning 'not initialize dans le foreach ci-dessous, bug pdt-extensions

// Initialise la liste des types présents dans les settings à importer
$types = [];
foreach($settings['types'] as $type) {
    $types[$type['name']] = $type['label'] . ' (' . $type['name'] . ')';
}

?>
<style>
    p.warning {
        font-size: larger;
        font-weight: bold;
    }
    p.warning b {
        color: #F00;
    }
</style>
<div class="wrap">
    <h1><?= sprintf(__('%s - importer des paramètres', 'docalist-data'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Les paramètres que vous avez collés contiennent les types listés ci-dessous.', 'docalist-data') ?>
        <?= __("Par défaut, <b>tous les types seront importés</b> mais vous pouvez décocher certains types pour n'importer qu'une partie des paramètres.", 'docalist-data') ?>
    </p>

    <?php
        $form = new Form();
        $form->checklist('types')
             ->setOptions($types)
             ->setLabel(__('Types à importer', 'docalist-data'))
             ->setDescription(__("Désélectionnez les types que vous ne voulez pas importer.", 'docalist-data'));

        $form->submit(__('Importer...', 'docalist-data'));
        $form->hidden('settings');

        $form->bind([
            'types' => array_keys($types),
            'settings' => json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ]);

        $form->display();
    ?>

    <p class="warning">
        <b>Attention :</b> en cliquant sur le bouton importer, vous allez écraser les paramètres de la base <b><?=$database->label()?></b>.
    </p>
    <p class="warning">
        Si la base <b><?=$database->label()?></b> contient déjà certains des types sélectionnés, <b>il seront écrasés et tous les réglages seront perdus</b> (tables d'autorité, grilles de saisie, libellés, descriptions, etc.)
    </p>
    <p class="warning">
        Assurez-vous que c'est la bonne base, les bons types, le bon sens de transfert, etc. : il n'y aura pas d'autre demande de confirmation...
    </p>

</div>
