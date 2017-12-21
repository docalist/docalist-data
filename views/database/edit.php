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
 * Edite les paramètres d'un base de données.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var int $dbindex L'index de la base.
 * @var string $error Erreur éventuelle à afficher.
 */
?>
<div class="wrap">
    <h1><?= __('Paramètres de la base', 'docalist-databases') ?></h1>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres de votre base de données :', 'docalist-databases') ?>
    </p>

    <?php if ($error) :?>
        <div class="error">
            <p><?= $error ?></p>
        </div>
    <?php endif ?>

    <?php
        // Récupère les settings pour déterminer la liste des analyseurs disponibles
        $settings = docalist('docalist-search-index-manager')->getIndexSettings();

        // Ne conserve que les analyseurs "texte"
        $analyzers = [];
        foreach(array_keys($settings['settings']['analysis']['analyzer']) as $analyzer) {
            if (strpos($analyzer, 'text') !== false) {
                $analyzers[] = $analyzer;
            }
        }

        $form = new Form();

        $form->tag('h2.title', __('Paramètres généraux', 'docalist-databases'));
        $form->tag('p', __('Options de publication de votre base de données.', 'docalist-databases'));
        $form->input('name')
             ->addClass('regular-text')
             ->setDescription(__('Nom de code interne de la base de données, de 1 à 14 caractères, lettres minuscules, chiffres et tiret autorisés.', 'docalist-databases'));
        $form->select('homepage')
             ->setOptions(pagesList())
             ->setFirstOption(false)
             ->setDescription(__("Choisissez la page d'accueil de votre base. Les références auront un permalien de la forme <code>/votre/page/12345/</code>.", 'docalist-databases'));
        $form->select('homemode')
             ->setLabel(__("La page d'accueil affiche", 'docalist-databases'))
             ->setOptions([
                 'page'     => __('Le contenu de la page WordPress', 'docalist-databases'),
                 'archive'  => __('Une archive WordPress de toutes les références', 'docalist-databases'),
                 'search'   => __('Une recherche docalist-search "*"', 'docalist-databases')
             ])
             ->setFirstOption(false)
             ->setDescription(__("Choisissez ce qui doit être affiché lorsque vous visitez la page d'accueil de votre base.", 'docalist-databases'));
        $form->select('searchpage')
             ->setOptions(pagesList())
             ->setFirstOption(false);

        $form->tag('h2.title', __('Fonctionnalités', 'docalist-databases'));
        $form->tag('p', __('Options et fonctionnalités disponibles pour cette base.', 'docalist-databases'));
        $form->checkbox('thumbnail');
        $form->checkbox('revisions');
        $form->checkbox('comments');

        $form->tag('h2.title', __('Indexation docalist-search', 'docalist-databases'));
        $form->tag('p', __("Options d'indexation dans le moteur de recherche.", 'docalist-databases'));
        $form->select('stemming')
             ->addClass('regular-text')
             ->setFirstOption(__('(Pas de stemming)', 'docalist-databases'))
             ->setOptions($analyzers);

        $form->tag('h2.title', __('Intégration dans WordPress', 'docalist-databases'));
        $form->tag('p', __("Apparence de cette base dans le back-office de WordPress.", 'docalist-databases'));
        $form->input('icon')
             ->addClass('medium-text')
             ->setDescription(sprintf(
                __('Icône à utiliser dans le menu de WordPress. Par exemple %s pour obtenir l\'icône %s.<br />
                    Pour choisir une icône, allez sur le site %s, faites votre voix et recopiez le nom de l\'icône.<br />
                    Remarque : vous pouvez également indiquer l\'url complète d\'une image, mais dans ce cas celle-ci ne s\'adaptera pas automatiquement au back-office de WordPress.',
                    'docalist-databases'),
                '<code>dashicons-book</code>',
                '<span class="dashicons dashicons-book"></span>',
                '<a href="https://developer.wordpress.org/resource/dashicons/#book" target="_blank">WordPress dashicons</a>'
            ));
        $form->input('label')
             ->addClass('regular-text');
        $form->textarea('description')
             ->setAttribute('rows', 2)
             ->addClass('large-text');

        $form->tag('h2.title', __('Autres informations', 'docalist-databases'));
        $form->tag('p', __('Informations pour vous.', 'docalist-databases'));
        $form->input('creation')
             ->setAttribute('disabled');
        $form->input('lastupdate')
             ->setAttribute('disabled');
        $form->textarea('notes')
             ->setAttribute('rows', 10)
             ->addClass('large-text')
             ->setDescription(__("Vous pouvez utiliser cette zone pour stocker toute information qui vous est utile : historique, modifications apportées, etc.", 'docalist-databases'));

        $form->submit(__('Enregistrer les modifications', 'docalist-databases'))
            ->addClass('button button-primary');

        !isset($database->creation) && $database->creation = date_i18n('Y/m/d H:i:s');
        !isset($database->lastupdate) && $database->lastupdate = date_i18n('Y/m/d H:i:s');
        !isset($database->stemming) && $database->stemming = 'fr-text';
        !isset($database->icon) && $database->icon = 'dashicons-list-view';
        !isset($database->thumbnail) && $database->thumbnail = true;
        !isset($database->revisions) && $database->revisions = true;
        !isset($database->comments) && $database->comments = false;

        $form->bind($database)->display('wordpress');
    ?>
</div>
<script type="text/javascript">
(function($) {
    /**
     * Si la base n'a pas de slug, change le slug quand on tape le nom
     */
    $(document).ready(function () {
        $(document).on('input propertychange', '#icon', function() {
            $('#icon-preview').remove();
            $('#icon').after('<span id="icon-preview" class="dashicons ' + $('#icon').val() + '" style="padding-left: 10px;font-size: 30px;"></span>');
        });
        $('#icon').trigger('input');

        $('#name').focus();
    });
}(jQuery));
</script>
<?php
/**
 * Retourne la liste hiérarchique des pages sous la forme d'un tableau
 * utilisable dans un select.
 *
 * @return array Un tableau de la forme PageID => PageTitle
 */
function pagesList() {
    $pages = ['…'];
    foreach(get_pages() as $page) { /** @var \WP_Post $page */
        $pages[$page->ID] = str_repeat('   ', count($page->ancestors)) . $page->post_title;
    }

    return $pages;
}
