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

namespace Docalist\Data;

use Docalist\Sequences;
use Docalist\Table\TableManager;
use Docalist\Type\Entity;
use Docalist\Schema\Schema;

use Docalist\Data\Indexable;
use Docalist\Data\Field\PostTypeField;
use Docalist\Data\Field\PostStatusField;
use Docalist\Data\Field\PostTitleField;
use Docalist\Data\Field\PostDateField;
use Docalist\Data\Field\PostAuthorField;
use Docalist\Data\Field\PostModifiedField;
use Docalist\Data\Field\PostPasswordField;
use Docalist\Data\Field\PostParentField;
use Docalist\Data\Field\PostNameField;
use Docalist\Data\Field\RefField;
use Docalist\Data\Field\TypeField;
use Docalist\Data\Field\SourceField;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;

use Docalist\Repository\Repository;

use Docalist\Forms\Container;

use ReflectionMethod;
use Docalist\Type\MultiField;
use Docalist\Data\Type\Group;

use Closure;
use InvalidArgumentException;
use Docalist\Data\Indexer\RecordIndexer;

use function Docalist\deprecated;

/**
 * Un enregistrement dans une base docalist.
 *
 * @property PostTypeField                  $posttype   Post Type
 * @property PostStatusField                $status     Statut de la fiche
 * @property PostTitleField                 $posttitle  Titre de la fiche
 * @property PostDateField                  $creation   Date/heure de création de la fiche.
 * @property PostAuthorField                $createdBy  Auteur de la fiche.
 * @property PostModifiedField              $lastupdate Date/heure de dernière modification
 * @property PostPasswordField              $password   Mot de passe de la fiche
 * @property PostParentField                $parent     Post ID de la fiche parent
 * @property PostNameField                  $slug       Slug de la fiche
 * @property RefField                       $ref        Numéro unique identifiant la fiche
 * @property TypeField                      $type       Type de fiche
 * @property IndexableTypedValueCollection  $source     Informations sur la source des données.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Record extends Entity implements Indexable
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'record',
            'label' => __('Type de base Docalist', 'docalist-data'),
            'description' => __('Type de base docalist-data.', 'docalist-data'),
            'fields' => [
                // Champs WordPress
                'posttype'      => PostTypeField::class,
                'status'        => PostStatusField::class,
                'posttitle'     => PostTitleField::class,
                'creation'      => PostDateField::class,
                'createdBy'     => PostAuthorField::class,
                'lastupdate'    => PostModifiedField::class,
                'password'      => PostPasswordField::class,
                'parent'        => PostParentField::class,
                'slug'          => PostNameField::class,

                // Champs docalist communs à tous les types d'entité
                'ref'           => RefField::class,
                'type'          => TypeField::class,
                'source'        => SourceField::class,
            ],
        ];
    }

    /**
     * Fait un "diff" entre les champs de deux types différents.
     *
     * La méthode retourne un tableau contenant la liste des champs du schéma du type $class1
     * qui ne figurent pas dans la liste des champs du schéma du type $class2.
     *
     * @param string $class1
     * @param string $class2
     *
     * @return array
     */
    private static function fieldsDiff($class1, $class2)
    {
        $schema1 = $class1::getDefaultSchema(); /* @var Schema $schema */
        $schema2 = $class2::getDefaultSchema(); /* @var Schema $parent */

        return array_diff_key($schema1->getFields(), $schema2->getFields());
    }

    /**
     * Supprime de la liste des champs passés en paramètre ceux qui sont marqués "unused" dans le schéma indiqué.
     *
     * @param array $fields
     * @param Schema $schema
     *
     * @return array La liste des champs épurée.
     */
    private static function removeUnused(array $fields, Schema $schema)
    {
        foreach (array_keys($fields) as $name) {
            if ($schema->hasField($name) && $schema->getField($name)->unused()) {
                unset($fields[$name]);
            }
        }

        return $fields;
    }

    /**
     * Retourne la liste des types dont hérite la classe en cours.
     *
     * @return array<int,class-string> Un tableau contenant le nom de classe complet des types dont hérite le type en cours, dans
     * l'ordre d'héritage (parent, grand-parent, etc.)
     *
     * Remarque : la classe de base des types (Type) n'est pas incluse dans le tableau retourné.
     */
    private static function getParentTypes(): array
    {
        $class = get_called_class();
        $parents = [];
        while ($class !== self::class) {
            $parents[] = $class;
            $class = get_parent_class($class);
            assert(is_string($class)); // false seulement si pas de parent, ne peut pas arriver
        }

        return $parents;
    }

    /**
     * Retourne la grille de base du type.
     *
     * La grille de base est identique au schéma du type, sauf qu'elle ne contient pas les champs qui sont
     * marqués "unused" dans le schéma.
     *
     * @return Schema
     */
    public static function getBaseGrid()
    {
        // C'est simplement le schéma par défaut sans les champs unused
        $schema = static::getDefaultSchema();

        $fields = self::removeUnused($schema->getFields(), $schema);
        foreach ($fields as & $field) {
            $field = $field->value();
        }

        return [
            'name' => 'base',
            'type' => $schema->type(),
            'gridtype' => 'base',
            'label' => $schema->label(),
            'description' => $schema->description(),
            'fields' => $fields,
        ];
    }

    /**
     * Retourne la grille de saisie par défaut du type.
     *
     * @return Schema
     */
    public static function getEditGrid()
    {
        // On part du type en cours
        $class = get_called_class();

        // Récupère la liste des champs de gestion (ceux définis par la classe Record)
        $recordFields = self::loadSchema()['fields'];

        // On construit le formulaire de saisie par défaut en regroupant les champs par niveau de hiérarchie
        $seen = $fields = [];
        $groupNumber = 1;
        while ($class !== self::class) {
            // Si le type en cours n'a pas surchargé loadSchema(), terminé (exemple : SvbType)
            $method = new ReflectionMethod($class, 'loadSchema');
            if ($method->class !== $class) {
                $class = get_parent_class($class);
                continue;
            }

            // Ajoute tous les champs qui sont dans le schéma du type en cours
            $schema = $class::loadSchema();
            $specific = [];
            foreach ($schema['fields'] as $name => $field) {
                if (isset($seen[$name])) {
                    continue;
                }
                if ($groupNumber > 1 && isset($recordFields[$name])) {
                    // Les champs de gestion (ceux qui sont déclarés dans Record) ne sont pas hérités automatiquement :
                    // si un type veut inclure un champ de gestion dans son formulaire de saisie, il doit le
                    // redéclarer explicitement dans son schéma. C'est ce que fait par exemple le type "Content", il
                    // redéclare le champ "posttitle" hérité de record.
                    continue;
                }

                $seen[$name] = true;
                if (isset($field['unused']) && $field['unused']) {
                    continue;
                }
                $specific[] = $name;
            }

            // Aucun champ, passe le niveau
            if (empty($specific)) {
                $class = get_parent_class($class);
                continue;
            }

            // Crée un groupe pour ce niveau
            $fields['group' . $groupNumber] = [
                'type' => Group::class,
                'label' => $schema['label'],
            ];
            ++$groupNumber;

            // Ajoute les champs spécifique à ce niveau
            $fields = array_merge($fields, $specific);

            // Passe au niveau suivant de la hiérarchie des types
            $class = get_parent_class($class);
        }

        // Ajoute les champs de gestion (type et ref) si on ne les a pas encore rencontrés
        $specific = [];
        foreach (['type', 'ref'] as $name) {
            !isset($seen[$name]) && $specific[] = $name;
        }

        if ($specific) {
            $fields['group' . $groupNumber] = [
                'type' => Group::class,
                'label' => __('Champs de gestion', 'docalist-data'),
                'state' => 'collapsed',
            ];
            $fields = array_merge($fields, $specific);
        }

        // Construit la grille finale
        /*
        $description = sprintf(__(
            "Saisie/modification d'une fiche '%s'.", 'docalist-data'),
            static::getDefaultSchema()->label()
        );
        */

        return [
            'name' => 'edit',
            'gridtype' => 'edit',
            'label' => __('Formulaire de saisie', 'docalist-data'),
            //'description' => $description,
            'fields' => $fields,
        ];
    }

    public static function getEditGridOLD()
    {
        // On part du schéma du type
        $schema = static::getDefaultSchema();

        // On construit le formulaire de saisie par défaut en regroupant les champs par niveau de hiérarchie
        $fields = [];
        $groupNumber = 1;
        foreach (self::getParentTypes() as $class) {
            // Détermine les champs spécifiques à ce type et supprime ceux qui ont été désactivé
            $parent = get_parent_class($class);
            $specific = self::removeUnused(self::fieldsDiff($class, $parent), $schema);

            // Aucun champ, passe le niveau
            if (empty($specific)) {
                continue;
            }

            // Crée un groupe pour ce niveau
            $level = $class::getDefaultSchema();
            $fields['group' . $groupNumber] = [
                'type' => Group::class,
                'label' => $level->label(),
            ];
            ++$groupNumber;

            // Ajoute les champs spécifique à ce niveau
            $fields = array_merge($fields, array_keys($specific));

            // Passe au niveau suivant
            $class = $parent;
        }

        // Ajoute un groupe pour les champs de gestion (type et ref uniquement, les autres sont gérés par wordpress)
        $fields['group' . $groupNumber] = [
            'type' => Group::class,
            'label' => 'Champs de gestion',
            'state' => 'collapsed',
        ];
//         $fields[] = 'posttitle';
        $fields[] = 'type';
        $fields[] = 'ref';
//         $fields = array_merge($fields, array_keys(self::loadSchema()['fields']));

        // Construit la grille finale
        $description = sprintf(__("Saisie/modification d'une fiche '%s'.", 'docalist-data'), $schema->label());

        return [
            'name' => 'edit',
            'gridtype' => 'edit',
            'label' => __('Formulaire de saisie', 'docalist-data'),
            'description' => $description,
            'fields' => $fields,
        ];
    }

    /**
     * Retourne la grille par défaut pour l'affichage long de ce type.
     *
     * @return Schema
     */
    public static function getContentGrid()
    {
        // On affiche un premier groupe contenant tous les champs (hérités ou non) de
        // l'entité (dans l'ordre) plus le champ ref.
        // Tous les autres champs (gestion) sont dans un groupe 2 qui n'est pas affiché.
        $schema = static::getDefaultSchema();

        // Groupe 1 : champs de l'entité + champ ref
        $fields = [];
        $fields['group1'] = [
            'type' => Group::class,
            'label' => __('Champs affichés', 'docalist-data'),
            'before' => '<dl>',
            'format' => '<dt>%label</dt><dd>%content</dd>',
            'after' => '</dl>'
        ];

        // Ajoute tous les champs de l'entité sauf les champs de gestion
        $all = self::removeUnused(self::fieldsDiff(get_called_class(), self::class), $schema);
        $fields = array_merge($fields, array_keys($all));

        // Ajoute le champ ref
        $fields[] = 'ref';

        // Groupe 2 : champs de gestion
        $fields['group2'] = [
            'type' => Group::class,
            'label' => __('Champs non affichés', 'docalist-data'),
        ];

        // Ajoute tous les champs de gestion (sauf ref, déjà affiché dans groupe 1)
        $management = self::loadSchema()['fields'];
        unset($management['ref']);
        $fields = array_merge($fields, array_keys($management));

        // Construit la grille finale
        $description = sprintf(__("Affichage détaillé d'une fiche '%s'.", 'docalist-data'), $schema->label());
        return [
            'name' => 'content',
            'gridtype' => 'display',
            'label' => __('Affichage long', 'docalist-data'),
            'description' => $description,
            'fields' => $fields,
        ];
    }

    /**
     * Retourne la grille par défaut pour l'affichage court de ce type.
     *
     * @return Schema
     */
    public static function getExcerptGrid()
    {
        // La grille courte par défaut n'affiche rien (groupe 1 vide) et wordpress affichera uniquement
        // le titre du post. Tous les champs sont dispos dans le groupe 2 qui n'affiche rien.

        // Groupe 1 : champs affichés (aucun)
        $fields = [];
        $fields['group1'] = [
            'type' => Group::class,
            'label' => __('Champs affichés', 'docalist-data'),
            'before' => '<dl>',
            'format' => '<dt>%label</dt><dd>%content</dd>',
            'after' => '</dl>'
        ];

        // Groupe 2 : champs masqués (tous)
        $fields['group2'] = [
            'type' => Group::class,
            'label' => __('Champs non affichés', 'docalist-data'),
        ];

        // Ajoute tous les champs dans le groupe 2
        $schema = static::getDefaultSchema();
        $all = self::removeUnused($schema->getFields(), $schema);
        $fields = array_merge($fields, array_keys($all));

        // Construit la grille finale
        $description = sprintf(__("Affichage court d'une fiche '%s'.", 'docalist-data'), $schema->label());
        return [
            'name' => 'excerpt',
            'gridtype' => 'display',
            'label' => __('Affichage court', 'docalist-data'),
            'description' => $description,
            'fields' => $fields,
        ];
    }

    /**
     * Initialise les champs de gestion de la notice juste avant qu'elle ne soit enregistrée.
     *
     * Les champs suivants sont initialisés :
     *
     * - ref : attribue un numéro de référence à la notice si elle est en statut "publish" et qu'elle n'a pas
     *   encore de numéro de référence (cf. initRefNumber).
     * - slug : attribue un slug à la notice si elle a un numéro de référence (cf. initSLug).
     * - posttitle : attribue le titre "(sans titre)" à la notice si elle n'a pas de titre (cf. initPostTitle).
     *
     * @param Repository $repository Le dépôt dans lequel l'entité va être enregistrée.
     */
    public function beforeSave(Repository $repository): void
    {
        parent::beforeSave($repository);

        // Initialise le numéro de réf
        $this->initRefNumber($repository);

        // Initialise le slug du post
        $this->initSlug();

        // Initialise le titre du post
        $this->initPostTitle();
    }

    /**
     * Initialise/vérifie le numéro de référence (champ ref) de la notice lorsque celle-ci est enregistrée.
     *
     * On alloue un numéro de référence à la notice si celle-ci est en statut publish et qu'elle n'a pas encore de
     * numéro. Sinon, on met à jour la séquence si le numéro de la notice est supérieur au dernier numéro alloué.
     *
     * @param Database $database La base dans laquelle la notice va être enregistrée (utilisé pour déterminer le
     * nom de la séquence).
     */
    protected function initRefNumber(Database $database)
    {
        // Si la notice a déjà un numéro de référence, on se contente de synchroniser la séquence
        if (isset($this->ref) && 0 !== $this->ref->getPhpValue()) {
            docalist(Sequences::class)->setIfGreater($database->getPostType(), 'ref', $this->ref->getPhpValue());

            return;
        }

        // Si la notice est en statut publish, on lui attribue un numéro de référence
        if (isset($this->status) && $this->status->getPhpValue() === 'publish') {
            $this->ref = docalist(Sequences::class)->increment($database->getPostType(), 'ref');
        }
    }

    /**
     * Initialise le slug (post_name) de la notice.
     *
     * Recopie le numéro de réf de la notice dans le champ slug (si la notice a un numéro), ne fait rien sinon.
     */
    protected function initSlug()
    {
        isset($this->ref) && $this->slug = $this->ref->getPhpValue();
    }

    /**
     * Initialise le champ post_title de la notice.
     *
     * Si aucun post_title n'a été indiqué dans le record, la méthode l'initialise avec la chaine "(sans titre)".
     * Les classes descendantes peuvent surcharger la méthode pour initialiser le champ post_title à partir des
     * informations qui figurent dans les autres champs de la notice (par exemple, la classe Organization récupère
     * le contenu du champ name).
     */
    protected function initPostTitle()
    {
        if (empty($this->posttitle)) {
            $this->posttitle = __('record sans titre)', 'docalist-data');
        }
    }

    public function getSettingsForm(): Container
    {
        $name = $this->getSchema()->name();
        $form = new Container($name);

        $form->hidden('name')->addClass('name');

        $form->input('label')
            ->setAttribute('id', $name . '-label')
            ->addClass('label regular-text')
            ->setLabel(__('Libellé', 'docalist-data'))
            ->setDescription(__('Libellé utilisé pour désigner ce type.', 'docalist-data'));

        $form->textarea('description')
            ->setAttribute('id', $name . '-description')
            ->addClass('description large-text autosize')
            ->setAttribute('rows', 1)
            ->setLabel(__('Description', 'docalist-data'))
            ->setDescription(__('Description du type.', 'docalist-data'));

        return $form;
    }


    public function getEditorSettingsForm(): Container
    {
        $schema = $this->getSchema();
        $name = $schema->name();
        $form = new Container($name);

        $form->hidden('name')->addClass('name');

        $form->input('label')
            ->setAttribute('id', $name . '-label')
            ->addClass('label regular-text')
            ->setAttribute('placeholder', $schema->label())
            ->setLabel(__('Titre', 'docalist-data'))
            ->setDescription(
                __('Titre du formulaire.', 'docalist-data') .
                ' ' .
                __("Par défaut, c'est le nom du type qui est utilisé.", 'docalist-data')
            );

        $form->textarea('description')
            ->setAttribute('id', $name . '-description')
            ->addClass('description large-text autosize')
            ->setAttribute('rows', 1)
            ->setAttribute('placeholder', $schema->description())
            ->setLabel(__('Introduction', 'docalist-data'))
            ->setDescription(
                __("Texte d'introduction qui sera affiché pour présenter le formulaire.", 'docalist-data') .
                ' ' .
                __("Par défaut, c'est la description du type qui est utilisée.", 'docalist-data') .
                ' ' .
                __("Indiquez '-' pour ne rien afficher.", 'docalist-data')
            );

        return $form;
    }

    public function getFormatSettingsForm(): Container
    {
        $name = $this->getSchema()->name();
        $form = new Container($name);

        $form->hidden('name')->addClass('name');

        $form->input('label')
            ->setAttribute('id', $name . '-label')
            ->addClass('label regular-text')
            ->setLabel(__('Nom du format', 'docalist-data'))
            ->setDescription(__("Libellé utilisé pour désigner ce format d'affichage.", 'docalist-data'));

        $form->textarea('description')
            ->setAttribute('id', $name . '-description')
            ->addClass('description large-text autosize')
            ->setAttribute('rows', 1)
            ->setLabel(__('Description', 'docalist-data'))
            ->setDescription(__("Description du format, notes, remarques... (champ de gestion)", 'docalist-data'));

        $form->input('before')
            ->setAttribute('id', $name . '-before')
            ->addClass('before regular-text')
            ->setLabel(__('Texte avant', 'docalist-data'))
            ->setDescription(__('Texte ou code html à afficher avant les données de la fiche.', 'docalist-data'));

        $form->input('after')
            ->setAttribute('id', $name . '-after')
            ->addClass('after regular-text')
            ->setLabel(__('Texte après', 'docalist-data'))
            ->setDescription(__('Texte ou code html à afficher les données de la fiche.', 'docalist-data'));

        return $form;
    }

    private function getFieldOption($field, $option, array $options, $default = null)
    {
        if (!empty($options['fields'][$field][$option])) {
            return $options['fields'][$field][$option];
        }
        $schema = $this->getSchema();
        if ($schema->hasField($field)) {
            if (! empty($value = $schema->getField($field)->__call($option))) {
                return $value;
            }
        }

        return $default;
    }

    public function getFormattedValue($options = null): string|array
    {
        // TEMP : pour le moment on peut nous passer une grille ou un schéma, à terme, on ne passera que des array
        $options && is_object($options) && $options = $options->value();

        $schema = $this->getSchema();

        // Récupère les noms des champs à afficher
        $fields = array_keys(isset($options['fields']) ? $options['fields'] : $schema->getFields());

        // Initialise les variables pour que cela fonctionne quand la grille ne commence pas par un groupe
        $format = '<p><b>%label</b>: %content</p>'; // Le format du groupe en cours
        $before = null;                             // Propriété 'before' du groupe en cours
        $sep = '';                                  // Propriété 'sep' du groupe en cours
        $after = null;                              // Propriété 'after' du groupe en cours
        $hasCap = true;                             // True si l'utilisateur a la cap requise par le groupe en cours
        $items = [];                                // Les items générés par le groupe en cours
        $result = '';                               // Le résultat final qui sera retourné

        // Formatte la notice
        foreach ($fields as $name) {
            // Récupère les options pour ce champ
            $field = isset($options['fields'][$name]) ? $options['fields'][$name] : [];

            // Si c'est un groupe, cela devient le nouveau groupe courant
            if (isset($field['type']) && $field['type'] === Group::class) {
                // Génère le groupe précédent si on a des items
                if ($items) {
                    $result .= $before . implode($sep, $items) . $after;
                    $items = [];
                }

                // Si le groupe requiert une capacité que l'utilisateur n'a pas, inutile d'aller plus loin
                // (le groupe ne peut pas figurer dans notre schéma de base, donc on ne teste que $field)
                if (isset($field['capability']) && ! current_user_can($field['capability'])) {
                    $hasCap = false;
                    continue;
                }

                // Stocke les propriétés du nouveau groupe en cours
                $hasCap = true;
                $format = isset($field['format']) ? $field['format'] : '';
                $before = isset($field['before']) ? $field['before'] : null;
                $sep = isset($field['sep']) ? $field['sep'] : '';
                $after = isset($field['after']) ? $field['after'] : null;
                continue;
            }

            // Ok, c'est un nouveau champ

            // Si on n'a pas la capacité du groupe en cours, ou si le format ou le champ sont vides, terminé
            if (! $hasCap || empty($format) || ! isset($this->phpValue[$name])) {
                continue;
            }

            // Si le champ requiert une capacité que l'utilisateur n'a pas, terminé
            $cap = isset($field['capability']) ? $field['capability'] : $schema->getField($name)->capability();
            if ($cap && ! current_user_can($cap)) {
                continue;
            }

            // Ok, formatte le contenu du champ
            $content = $this->phpValue[$name]->getFormattedValue($field);

            // Champ renseigné mais format() n'a rien retourné, passe au champ suivant
            if (empty($content)) {
                continue;
            }

            // format() nous a retourné soit un tableau de champs (vue éclatée), soit une simple chaine
            // avec le contenu formatté du champ. Si c'est une chaine, on le gère comme un tableau en
            // utilisant le libellé du champ.
            if (! is_array($content)) {
                $label = isset($field['label']) ? $field['label'] : $schema->getField($name)->label();
                ($label === '-') && $label = '';
                $content = [$label => $content];
            }

            // Stocke le champ (ou les champs en cas de vue éclatée)
            $fieldBefore = isset($field['before']) ? $field['before'] : $schema->getField($name)->before();
            $fieldAfter = isset($field['after']) ? $field['after'] : $schema->getField($name)->after();
            foreach ($content as $label => $content) {
                $content = $fieldBefore . $content . $fieldAfter;
                $items[] = strtr($format, ['%field' => $name, '%label' => $label, '%content' => $content]);
            }
        }

        // Génère le groupe en cours si on a des items
        $items && $result .= $before . implode($sep, $items) . $after;

        // Terminé
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexerClass(): string
    {
        return RecordIndexer::class;
    }

    /**
     * Indexation standard d'un champ multifield répétable.
     *
     * Génère un champ field-type.
     *
     * @param array $document Document ElasticSearch à modifier.
     * @param string $field Nom du champ à mapper.
     * @param string|Closure $value Nom du sous-champ contenant la valeur à indexer ('value' par défaut) ou
     * une fonction chargée de retourner le contenu à indexer.
     * Exemple : function(TypedText $item) { return $item->text->getPhpValue(); }
     *
     * @deprecated
     */
    protected function mapMultiField(array & $document, $field, $value = 'value')
    {
        deprecated(get_class($this) . '::mapMultiField()', '', '2019-06-06');

        if (isset($this->$field)) {
            foreach ($this->$field as $item) { /* @var MultiField $item */
                $code = $item->getCategoryCode();
                $key = $code ? ($field . '-' . $code) : $field;
                //$content = $item->$value->getPhpValue();
                $content = is_string($value) ? $item->$value->getPhpValue() : $value($item);
                if (isset($document[$key])) {
                    $content = array_merge((array) $document[$key], (array) $content);
                    $content = array_values(array_unique($content));
                }
                is_array($content) && count($content) === 1 && $content = array_shift($content);
                $document[$key] = $content;
            }
        }
    }

    /**
     * Recherche le code de tous les topics qui sont associés à une table de type 'thesaurus'.
     *
     * @return string[] Un tableau de la forme table => topic (les clés indiquent la table utilisée).
     *
     * @deprecated Utiliser Topics::getThesaurusTopics() à la place.
     */
    protected function getThesaurusTopics()
    {
        deprecated(get_class($this) . '::getThesaurusTopics()', '$record->topic->getThesaurusTopics()', '2017-07-05');

        return $this->topic->getThesaurusTopics();
    }

    /**
     * Détermine le path complet des termes passés en paramètre dans le thesaurus indiqué.
     *
     * @param array $terms Liste des termes à traduire.
     * @param string $table Nom de la table d'autorité à utiliser (doit être de type 'thesaurus').
     *
     * @return string[] Le path complet des termes.
     *
     * @deprecated
     */
    protected function getTermsPath(array $terms, $tableName)
    {
        deprecated(get_class($this) . '::getTermsPath()', '$record->topic->getTermsPath()', '2019-06-06');

        // Ouvre le thesaurus
        /** @var TableManager */
        $tableManager = docalist(TableManager::class);
        $table = $tableManager->get($tableName);

        // Pour chaque terme ajoute le terme parent comme préfixe tant qu'on a un terme parent
        foreach ($terms as & $term) {
            $path = $term;
            $seen = [$term => true];
            while (!empty($term = $table->find('BT', 'code=' . $table->quote($term)))) {
                // Exit si les BT forment une boucle infinie
                if (isset($seen[$term])) {
                    printf(
                        '<p style="color:red">Thésaurus "%s" : boucle infinie sur les BT des termes "%s" (%s)</p>',
                        $tableName,
                        $path,
                        __METHOD__
                    );
                    break;
                }
                $seen[$term]= true;

                // find() retourne null si pas de BT ou false si pas de réponse (erreur dans le theso)
                $path = $term . '/' . $path;
            }
            $term = $path;
        }

        // Ok
        return $terms;
    }
}
