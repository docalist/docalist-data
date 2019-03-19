<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Views;

use Docalist\Data\Export\AdminPage\SettingsPage;
use Docalist\Data\Export\Settings\ExportSettings;
use Docalist\Forms\Form;

/**
 * Paramètres de l'export.
 *
 * @var SettingsPage    $this
 * @var ExportSettings  $settings Les paramètres pour l'export.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
?>
<div class="wrap">
    <h1><?= __('Export Docalist', 'docalist-data') ?></h1>

    <p class="description"><?php
        _e(
            "L'export Docalist vous permet de générer des fichiers et des documents à partir des résultats
            d'une recherche docalist-search.",
            'docalist-data'
        );
    ?></p>

    <?php

        $form = new Form();

        $form->tag('h2.title', __("Activation de l'export", 'docalist-search'));
        $description = sprintf(
            __(
                'Pour activer l\'export docalist, vous devez choisir une page de votre site qui servira de point
                d\'entrée pour lancer l\'export. Vous pouvez choisir une page existante dans la liste ci-dessous
                ou <a href="%s">créer une nouvelle page</a> et revenir ici ensuite.
                <br />
                Remarque : le contenu de la page n\'est pas très important (il peut même être vide) car il ne
                sera pas affiché lorsque vous  visiterez la page (docalist affichera à la place les options
                disponibles). Cependant, le contenu est susceptible d\'être affiché ailleurs sur votre site
                (dans une recherche, dans un flux RSS...) donc il est préférable d\'indiquer un contenu minimal.',
                'docalist-data'
            ),
            esc_attr(admin_url('post-new.php?post_type=page'))
        );
        $form->tag('p.description', $description);

        $form->select('exportpage')->setOptions(pagesList())->setFirstOption(false);

        $form->tag('h2.title', __("Autorisations", 'docalist-search'));

        $description =  __(
            'Par défaut, personne n\'est autorisé à exporter vos données : vous devez définir explicitement
            dans la liste ci-dessous chacun des groupes utilisateurs (rôles WordPress) pour lesquels l\'export
            sera disponible. Pour chaque groupe, vous devez également indiquer le nombre maximum de contenus qui
            peuvent être exportées en une seule étape.',
            'docalist-data'
        );
        $form->tag('p.description', $description);

        $table = $form->table('limit')->addClass('limit')->setRepeatable();
        $table->select('role')->setOptions(userRoles());
        $table->input('limit')->setAttribute('type', 'number');

        $form->submit(__('Enregistrer les modifications', 'docalist-data'))
             ->addClass('button button-primary');

        $form->bind($settings)->display();
    ?>
</div>

<?php
/**
 * Retourne la liste hiérarchique des pages sous la forme d'un tableau
 * utilisable dans un select.
 *
 * @return array Un tableau de la forme PageID => PageTitle
 */
function pagesList()
{
    $pages = ['…'];
    foreach (get_pages() as $page) { /* @var \WP_Post $page */
        $pages[$page->ID] = str_repeat('   ', count($page->ancestors)) . $page->post_title;
    }

    return $pages;
}

/**
 * Retourne la liste de rôles WordPress.
 *
 * @return array role => label
 */
function userRoles()
{
    global $wp_roles;

    $roles = array_map('translate_user_role', $wp_roles->get_names());
    $roles['(anonymous)'] = __('Visiteur non connectés', 'docalist-data');

    return $roles;
}
