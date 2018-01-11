<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Settings;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\Integer;
use Docalist\Type\Boolean;
use Exception;

/**
 * Paramètres d'une base de données.
 *
 * Une base est essentiellement une liste de types.
 *
 * @property Text $name Nom de la base de données.
 * @property Integer $homepage ID de la page d'accueil de la base de données.
 * @property Text $homemode Mode de fonctionnement de la page d'accueil (page, archive ou search).
 * @property Integer $searchpage ID de la page liste des réponses.
 * @property Text $label Libellé de la base.
 * @property Text $description Description de la base.
 * @property Text $stemming Stemming / analyseur par défaut.
 * @property TypeSettings[] $types Types de notices gérés dans cette base, indexés par nom.
 * @property Text $creation Date de création de la base.
 * @property Text $lastupdate Date de dernière modification des paramètres de la base.
 * @property Text $icon Icône à utiliser pour cette base.
 * @property Text $notes Notes et historique de la base.
 * @property Boolean $thumbnail Indique si les notices peuvent avoir une image à la une.
 * @property Boolean $revisions Indique si les modifications des notices font l'objet de révisions.
 * @property Boolean $comments Indique si les notices peuvent avoir des commentaires.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DatabaseSettings extends Composite
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'name' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Nom de la base', 'docalist-data'),
                    'description' => __('Nom de code interne de la base de données.', 'docalist-data'),
                ],

                'homepage' => [
                    'type' => 'Docalist\Type\Integer',
                    'label' => __("Page d'accueil", 'docalist-data'),
                    'description' => __("Page d'accueil de la base.", 'docalist-data'),
                ],

                'homemode' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Mode accueil', 'docalist-data'),
                    'description' => __("Mode de fonctionnement de la page d'accueil.", 'docalist-data'),
                ],

                'searchpage' => [
                    'type' => 'Docalist\Type\Integer',
                    'label' => __('Page liste des réponses', 'docalist-search'),
                    'description' => __(
                        'Page WordPress sur laquelle sont affichées les recherches dans cette base.',
                        'docalist-search'
                    ),
                ],

                'label' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Libellé à afficher', 'docalist-data'),
                    'description' => __(
                        'Libellé affiché dans les menus et dans les pages du back-office.',
                        'docalist-data'
                    ),
                ],

                'description' => [
                    'type' => 'Docalist\Type\LargeText',
                    'label' => __('Description', 'docalist-data'),
                    'description' => __('Description de la base.', 'docalist-data'),
                ],

                'stemming' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Stemming', 'docalist-data'),
                    'description' => __(
                        'Stemming qui sera appliqué aux champs textes des notices.',
                        'docalist-data'
                    ),
                    'default' => 'fr',
                ],
                'types' => [
                    'type' => 'Docalist\Data\Settings\TypeSettings*',
                    'key' => 'name',
                    'label' => __('Types de notices gérés dans cette base', 'docalist-data'),
                ],
                'creation' => [
                    'type' => 'Docalist\Type\DateTime',
                    'label' => __('Date de création', 'docalist-data'),
                    'description' => __('Date/heure de création de la base.', 'docalist-data'),
                ],

                'lastupdate' => [
                    'type' => 'Docalist\Type\DateTime',
                    'label' => __('Dernière modification', 'docalist-data'),
                    'description' => __(
                        'Date/heure de dernière modification des paramètres de la base.',
                        'docalist-data'
                    ),
                ],

                'icon' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Icône', 'docalist-data'),
                    'default' => 'dashicons-feedback',
                    'description' => __(
                        'Nom de la dashicon affichée dans les menus WordPress.',
                        'docalist-data'
                    ),
                ],

                'notes' => [
                    'type' => 'Docalist\Type\LargeText',
                    'label' => __('Notes et historique', 'docalist-data'),
                    'description' => __('Notes pour les administrateurs.', 'docalist-data'),
                ],

                'thumbnail' => [
                    'type' => 'Docalist\Type\Boolean',
                    'label' => __('Image à la une', 'docalist-data'),
                    'description' => __('Les références peuvent avoir une image à la une.', 'docalist-data'),
                ],

                'revisions' => [
                    'type' => 'Docalist\Type\Boolean',
                    'label' => __('Activer les révisions', 'docalist-data'),
                    'description' => __(
                        'Journaliser les modifications apportées aux références.',
                        'docalist-data'
                    ),
                ],

                'comments' => [
                    'type' => 'Docalist\Type\Boolean',
                    'label' => __('Activer les commentaires', 'docalist-data'),
                    'description' => __('Les références peuvent avoir des commentaires.', 'docalist-data'),
                ],
            ],
        ];
    }

    /**
     * Valide les propriétés de la base.
     *
     * Retourne true si tout est correct, génère une exception sinon.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function validate()
    {
        if (!preg_match('~^[a-z][a-z0-9-]{1,13}$~', $this->name())) {
            throw new Exception(__('Le nom de la base est invalide.', 'docalist-data'));
        }

        $this->label = strip_tags($this->label());
        $this->label() === '' && $this->label = $this->name;

        return true;
    }

    /**
     * Retourne le nom du "custom post type" WordPress de cette base.
     *
     * @return string
     */
    public function postType()
    {
        return 'db' . $this->name();
    }

    /**
     * Retourne le slug de la page d'accueil de la base.
     *
     * @return string
     */
    public function slug()
    {
        return get_page_uri($this->homepage());
    }

    /**
     * Retourne l'url de la page d'accueil de la base.
     *
     * @return string
     */
    public function url()
    {
        return get_permalink($this->homepage());
    }

    /**
     * Retourne toutes les capacités liées à la base de données.
     *
     * Utilise get_post_type_capabilities() pour laisser WordPress générer les
     * droits standards.
     *
     * @return array Retourne un tableau de capacités dans le format attendu
     * par register_post_type().
     */
    public function capabilities()
    {
        $cap = $this->capabilitySuffix();

        return (array) get_post_type_capabilities((object) [
            'capability_type' => [$cap, "{$cap}s"],
            'map_meta_cap' => true,
            'capabilities' => [
                // Droit "LIRE" :
                // --------------
                // Par défaut, tout le monde peut voir les notices car :
                // - 'read_post' est mappé vers 'read_{$cap}' et dans la
                //   fonction map_meta_cap (wp_includes/capabilities.php,
                //   case 'read_post' autour de la ligne 1234), wordpress teste
                //   si l'utilisateur dispose de la capacité 'read'.
                // - 'read' est mappé vers 'read' par défaut, donc tous ceux
                //   qui ont ce droit peuvent voir les notices de la base.
                //
                // On pourrait vouloir limiter la consultation des notices à
                // certaines personnes. Dans ce cas, on pourrait :
                // - créer un droit générique 'read_{$cap}s' qu'il faudrait
                //   attribuer aux personnes qui ont le droit de voir les refs
                // - mapper le droit standard 'read' vers 'read_{$cap}s'
                //
                // Dans le back-office, cela fonctionner : en mode 'détail',
                // seules les personnes qui ont le droit peuvent voir le résumé
                // des notices (cependant, wpfront ne reconnaît pas cette cap
                // comme une cap standard wp et l'affiche en "other caps").
                //
                // Par contre, ce n'est testé nulle part ailleurs : en front
                // office, les thèmes ne testent pas si on a le droit 'read', et
                // donc on a de toute façon accès aux notices : page d'archives,
                // une recherche, affichage long, etc.
                //
                // Au final, on ne crée donc aucun droit spécifique, et on
                // laisse WP faire son mappage par défaut, à savoir :
                // 'read_post' => "read_{$cap}", // par défaut
                // 'read' => 'read'
                //
                // Droit "CRER UNE REF" :
                // ----------------------
                // Par défaut, create_posts est simplement mappé vers edit_posts
                // On pourrait faire le mappage nous mêmes pour disposer d'un
                // droit spécifique, différent de 'edit_post' (i.e. certains
                // peuvent créer mais pas éditer, certains peuvent éditer mais
                // pas créer, etc.) :
                // 'create_posts' => "create_{$cap}s",
                //
                // Mais en fait, ça ne marche pas car pour un CPT, on ne peut
                // pas distinguer 'edit_post' de 'create_post' : si on a l'un,
                // on a l'autre.
                //
                // C'est un bug WordPress connu :
                // http://herbmiller.me/2014/09/21/wordpress-capabilities-restrict-add-new-allowing-edit/
                // https://core.trac.wordpress.org/ticket/29714
                // https://core.trac.wordpress.org/ticket/22895
                //
                // Dans user_can_access_admin_page(), wordpress teste si
                // l'utilisateur encours a le droits d'accéder à la page du menu.
                // Mais quand il teste la page edit.php?post_type=dbprisme
                // il utilise $pagenow qui vaut edit.php tout court.
                // Donc il teste si on a le droit indiqué (edit_posts) et comme
                // ce n'est pas le cas, il nous refuse.
                // Le bug, c'est que pagenow ne contient pas le bon truc...

                // Droit supplémentaire : importer des notices dans la base
                'import' => "import_{$cap}s",
            ],

        ]);
    }

    /**
     * Retourne le suffixe utilisé pour les droits spécifiques à cette base.
     *
     * Tous les droits spécifiques à une base contiennent le nom de cette
     * base suivi du suffixe 'ref' ou 'refs' (exemple : create_dbprisme_refs).
     * - Les "primary caps" finissent par "_refs" (au pluriel)
     * - Les "meta caps" finissent par "_ref" (au singulier)
     *
     * Dans la gestion des rôles et des droits, seuls des primary caps doivent
     * être accordées. Les meta caps sont des pseudo droits qui sont mappés en
     * fonction de la notice à laquelle ils sont appliqués.
     *
     * Le suffixe retourné par la méthode est le préfixe utilisé pour les
     * meta capabilities (au singulier donc).
     *
     * Il suffit d'ajouter un "s" pour obtenir le suffixe utilisé pour les
     * "primary capabilities".
     *
     * @return string
     */
    public function capabilitySuffix()
    {
        return $this->postType() . '_ref';
    }
}
