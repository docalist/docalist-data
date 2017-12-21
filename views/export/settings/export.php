<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2015-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Databases\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Export\Views;

use Docalist\Databases\Export\Settings;
use Docalist\Forms\Form;

/**
 * Paramètres de l'export.
 *
 * @var SettingsPage $this
 * @var Settings     $settings Les paramètres pour l'export.
 */
?>
<style>
.limit table.field-table { width: auto; }
</style>
<div class="wrap">
    <h1><?= __('Export et bibliographies', 'docalist-databases') ?></h1>

    <p class="description"><?php
        echo __(
            "Le module d'export vous permet de générer des fichiers d'export et des bibliographies à partir d'une recherche docalist-search ou du panier de notices.",
            'docalist-databases'
        );
    ?></p>

    <?php
        $form = new Form();

        $form->select('exportpage')
             ->setOptions(pagesList())
             ->setFirstOption(false);

        $form->table('limit')->addClass('limit')->setRepeatable()
                ->select('role')->setOptions(userRoles())->getParent()
                ->input('limit')->setAttribute('type', 'number');

        $form->submit(__('Enregistrer les modifications', 'docalist-databases'))
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
    foreach (get_pages() as $page) { /** @var \WP_Post $page */
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

    return array_map('translate_user_role', $wp_roles->get_names());
}
