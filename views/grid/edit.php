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
use Docalist\Forms\Metabox;
use Docalist\Forms\Container;
use Docalist\Data\Type\Group;
use Docalist\Type\Composite;
use Docalist\Data\Indexable;
use Docalist\Data\Indexer;

/**
 * Edite une grille.
 *
 * Cette vue prend en entrée les paramètres suivant :
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

/**
 * Crée récursivement le formulaire de paramétrage de la grille, des champs, des sous-champs, etc.
 *
 * @param Schema $schema
 * @param Schema $grid
 * @param string $method
 *
 * @return Metabox
 */
function createForm(Schema $schema, Schema $grid, $method = 'getSettingsForm')
{
    static $level = 0;
    static $prefix = '';

    ++$level;
    $savPrefix = $prefix;

    // Récupère le formulaire de saisie des propriétés du champ
    $type = $schema->collection() ?: $schema->type() ?: Composite::class;
    $field = new $type($type::getClassDefault(), $schema);
    $form = $field->$method(); /* @var Container $form */

    // Détermine le nom de la metabox (vide au premier niveau, nom du champ/sous-champ ensuite)
    $name = '';
    if ($level > 1) {
        $name = $grid->name() ;
        $prefix = ltrim($prefix . '.' . $name, '.');
    }

    // Détermine le titre de la metabox
//     $label = $prefix ?: $grid->label();
//     $label = sprintf('%s - <span class="label">%s</span>', $prefix, $grid->label() ?: $schema->label());
    $label = sprintf('<span>%s</span>', $grid->label() ?: $schema->label());
    $prefix && ($grid->type() !== Group::class) && $label .= " <small>($prefix)</small>";

    // Détermine les classes CSS à appliquer à la metabox
    $type = $schema->type() ?: Composite::class;
    $type = strtolower(substr($type, strrpos($type, '\\') + 1));
    $class = $type . ' ' . $grid->name() . ' level' . $level . ($level > 1 ? ' closed' : '');

    // Crée la metabox et ajoute tous les champs du formulaire
    $metabox = new Metabox($name);
    $metabox->setLabel($label)->setAttribute('class', $class)->addItems($form->getItems());

    // Crée une "sous-metabox" pour chacun des sous-champs, dans l'ordre choisi par l'utilisateur
    $fields = $grid->getFields();
    if ($fields) {
        $form = $metabox->container('fields')
            ->setLabel(__('Champs', 'docalist-data'))
            ->addClass('meta-box-sortables');
        foreach ($fields as $name => $subfield) {
            // si c'est un groupe, il n'est que dans la grille, pas dans le schéma, on prend le schéma du groupe
            $fieldSchema = $schema->hasField($name) ? $schema->getField($name) : $subfield;
            $form->add(createForm($fieldSchema, $subfield, $method));
        }
    }

    // Valeur par défaut
    if ($level === 2 && $method === 'getEditorSettingsForm' && $schema->type() !== Group::class) {
        $default = $field->getEditorForm($grid)
            ->setName('default')
            ->setLabel(__('Valeur par défaut', 'docalist-data'));
        $default->setRequired('');
        $metabox->add($default);
    }
    // TODO : ça devrait être getSettingsForm/getEditorSettingsForm qui se charge d'insérer l'éditeur
    // dans le formulaire. ça permettrait de dire qu'on en veut pour les grilles base/edit et chaque
    // champ pourrait choisir (par exemple, on ne veut pas de valeur par défaut pour les champs de gestion
    // comme post_type ou date).

    if ($method === 'getSettingsForm' && $field instanceof Indexable) {
        $class = $field->getIndexerClass();
        $indexer = new $class($field); /** @var Indexer $indexer */
        $metabox->add($indexer->getIndexSettingsForm());
    }


    $prefix = $savPrefix;
    --$level;

    return $metabox;
}

// Méthode à utiliser pour créer le formulaire en fonction du type de la grille
$methods = [
    'base'    => 'getSettingsForm',
    'edit'    => 'getEditorSettingsForm',
    'display' => 'getFormatSettingsForm',
];

// Crée le formulaire
$form = createForm($type->grids['base'], $grid, $methods[$grid->gridtype()]);
$form->bind($grid->getPhpValue());

// Enqueue les assets de l'éditeur de grille
wp_styles()->enqueue(['docalist-data-grid-edit']);
wp_scripts()->enqueue(['docalist-data-grid-edit']);

// Enqueue la css spécifique du type pour le formulaire de saisie
if ($grid->gridtype() === 'edit') {
    $css = $type->grids['edit']->stylesheet();
    !empty($css) && wp_styles()->enqueue([$css]);
}

?>
<div class="wrap">
    <h1><?= sprintf(__('%s - %s - %s', 'docalist-data'), $database->label(), $type->label(), $grid->label()) ?></h1>

    <p class="description">
        <?= __('L\'écran ci-dessous vous permet de personnaliser la grille.', 'docalist-data') ?>
        <?= __('Cliquez sur un champ pour afficher et modifier ses propriétés.', 'docalist-data') ?>
        <?= __('Utilisez le bouton "Ajouter un groupe" pour créer un nouveau groupe de champs.', 'docalist-data') ?>
        <?= __('Vous pouvez également modifier l\'ordre des champs et les déplacer d\'un groupe à un autre en faisant un glisser/déposer.', 'docalist-data') ?>
    </p>
    <form action ="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">

                <div id="post-body-content" style="position: relative;">
                    <div class="grid <?=$gridname?> metabox-holder">
                        <?php $form->display('wordpress') ?>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h3 class="hndle">Enregistrer</h3>
                        <div class="inside">
                            <p class="buttons">
                                <button type="submit" class="button button-primary">
                                    <?= __('Enregistrer les modifications', 'docalist-data') ?>
                                </button>
                            </p>
                        </div>
                    </div>
                    <?php if ($gridname !== 'base') :?>
                        <div class="postbox">
                            <h3 class="hndle">Outils</h3>
                            <div class="inside">
                                <button type="button" class="button add-group">
                                    <?= __('Ajouter un groupe de champs', 'docalist-data') ?>
                                </button>
                            </div>
                        </div>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </form>

    <!-- Template utilisé pour créer de nouveaux groupes. -->
    <script type="text/html" id="group-template"><?php // Pas d'espace avant le début du formulaire sinon on a un warning jqueryMigrate "$.html() must start with '<'"
            $schema = new Schema([
                // Valeurs par défaut communes
                'type' => Group::class,
                'name' => 'group{group-number}',
                'label' => __('Nouveau groupe de champs', 'docalist-data'),

                // Valeurs par défaut pour grille de saisie
                'state' => '', // = normal

                // Valeurs par défaut pour grille d'affichage
                'before' => '<dl>',
                'format' => '<dt>%label</dt><dd>%content</dd>',
                'after' => '</dl>',
            ]);

            $group = createForm($schema, $schema, $methods[$grid->gridtype()]);
            $group->bind($schema->getPhpValue());

            $group->removeClass('level1')->addClass('level2')->setName($schema->name()); // au level 1, createForm ne génère pas de nom

            $form->get('fields')->add($group); // pour que les champs aient le bon nom
            $group->display('wordpress')
    ?></script>
</div>
