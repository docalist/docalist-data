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

use Docalist\Views;
use Docalist\Data\Database;
use Docalist\Data\Type;
use Docalist\Data\Settings\Settings;
use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Entity\ContentEntity;
use Docalist\Data\Export\ExportSetup;
use InvalidArgumentException;

use function Docalist\deprecated;

/**
 * Plugin de gestion des bases docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Plugin
{
    /**
     * La configuration du plugin.
     *
     * @var Settings
     */
    protected $settings;

    /**
     * La liste des bases
     *
     * @var Database[]
     */
    protected $databases;

    /**
     * Initialise le plugin.
     */
    public function __construct()
    {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-data', false, 'docalist-data/languages');

        // Debug - permet de réinstaller les tables docalist-data
        if (isset($_GET['reinstall-tables']) && $_GET['reinstall-tables'] === 'docalist-data') {
            $installer = new Installer();
            echo 'Uninstall docalist-data tables...<br />';
            $installer->deactivate();
            echo 'Reinstall docalist-data tables...<br />';
            $installer->activate();
            echo 'Done.';
            die();
        }

        // Ajoute notre répertoire "views" au service "docalist-views"
        add_filter('docalist_service_views', function (Views $views) {
            return $views->addDirectory('docalist-data', DOCALIST_DATA_DIR . '/views');
        });

        // Initialise la liste des bases. On le fait dans l'action init car on ne peut pas appeller
        // register_post_type / add_rewrite_tag avant
        add_action('init', function () {
            // Charge la configuration du plugin
            $this->settings = new Settings(docalist('settings-repository'));

            // Crée les bases de données définies par l'utilisateur
            $this->databases = array();
            foreach ($this->settings->databases as $settings) {
                /* @var DatabaseSettings $settings */
                $database = new Database($settings);
                $this->databases[$database->getPostType()] = $database;
            }
        });

        // Crée la page Réglages » Docalist-Databases
        add_action('admin_menu', function () {
            new AdminDatabases($this->settings);
        });

        // Déclare la liste des types définis dans ce plugin
        add_filter('docalist_databases_get_types', function (array $types) {
            return $types + [
                'content' => ContentEntity::class,
            ];
        });

        // Déclare nos assets
        require_once dirname(__DIR__) . '/assets/register.php';

        // Initialise le module d'export
        ExportSetup::setup();

        // Autorise l'upload de fichier JSON
        add_filter('upload_mimes', function(array $types) {
            return $types + [
                'json' => 'application/json; charset=utf-8'
            ];
        });
    }

    /**
     * Retourne la liste des bases de données définies.
     *
     * @return Database[]
     */
    public function databases()
    {
        return $this->databases;
    }

    /**
     * Retourne la base de données ayant le post type indiqué.
     *
     * @param string $postType Le post type de la base recherchée.
     *
     * @return Database|null Retourne l'objet Database ou null si la base
     * indiquée n'existe pas.
     */
    public function database($postType)
    {
        return isset($this->databases[$postType]) ? $this->databases[$postType] : null;
    }

    /**
     * Retourne l'enregistrement dont l'id est passé en paramètre.
     *
     * @deprecated Utilisez getRecord() à la place
     *
     * @param string $id POST_ID de la référence à charger.
     *
     * @return Record
     *
     * @throws InvalidArgumentException
     */
    public function getReference($id = null)
    {
        deprecated(get_class($this) . '::getReference()', 'getRecord()', '2018-02-09');

        return $this->getRecord($id);
    }

    /**
     * Retourne l'enregistrement dont l'id est passé en paramètre.
     *
     * Implémentation du filtre 'docalist_databases_get_reference'.
     *
     * @param int|null $id POST_ID de la référence à charger.
     *
     * @return Record
     *
     * @throws InvalidArgumentException
     */
    public function getRecord($id = null)
    {
        is_null($id) && $id = get_the_ID();
        $type = get_post_type($id);

        if (false === $type) {
            throw new InvalidArgumentException(sprintf('Post %s not found', $id));
        }

        if (! isset($this->databases[$type])) {
            $msg = __("Post %s is not a Docalist record (postype=%s)");
            throw new InvalidArgumentException(sprintf($msg, $id, $type));
        }

        $database = $this->databases[$type]; /* @var Database $database */
        return $database->load($id);
    }
}
