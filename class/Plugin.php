<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data;

use Docalist\Views;
use Docalist\Data\Database;
use Docalist\Data\Type;
use Docalist\Data\Settings\Settings;
use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Export\ExportService;
use Docalist\Data\Entity\Content;
use Exception;

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

        // Debug - permet de réinstaller les tables SVB
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

        // Déclare le service "docalist-data-export"
        docalist('services')->add('docalist-data-export', new ExportService());

        add_action('init', function () {
            // Charge la configuration du plugin
            $this->settings = new Settings(docalist('settings-repository'));

            // Crée les bases de données définies par l'utilisateur
            $this->databases = array();
            foreach ($this->settings->databases as $settings) {
                /** @var DatabaseSettings $settings */
                $database = new Database($settings);
                $this->databases[$database->postType()] = $database;
            }
        });

        // Crée la page Réglages » Docalist-Databases
        add_action('admin_menu', function () {
            new AdminDatabases($this->settings);
        });

        // Déclare la liste des types définis dans ce plugin
        add_filter('docalist_databases_get_types', function (array $types) {
            $types += [
                'content' => Content::class,
            ];

            return $types;
        });

        // Nos filtres
        add_filter('docalist_databases_get_reference', array($this, 'getReference'));

        // Liste des exporteurs définis dans ce plugin
        add_filter('docalist_databases_get_export_formats', function (array $formats) {
            $formats['docalist-json'] = [
                'label' => 'Docalist JSON',
                'description' => 'Fichier JSON compact, notices en format natif de Docalist.',
                'converter' => 'Docalist\Data\Export\Docalist',
                'exporter' => 'Docalist\Data\Export\Json',
            ];

            $formats['docalist-json-pretty'] = [
                'label' => 'Docalist JSON formatté',
                'description' => 'Fichier JSON formatté et indenté, notices en format natif de Docalist.',
                'converter' => 'Docalist\Data\Export\Docalist',
                'exporter' => 'Docalist\Data\Export\Json',
                'exporter-settings' => [
                    'pretty' => true,
                ],
            ];

            $formats['docalist-xml'] = [
                'label' => 'Docalist XML',
                'description' => 'Fichier XML compact, notices en format natif de Docalist.',
                'converter' => 'Docalist\Data\Export\Docalist',
                'exporter' => 'Docalist\Data\Export\Xml',
            ];

            $formats['docalist-xml-pretty'] = [
                'label' => 'Docalist XML formatté',
                'description' => 'Fichier XML formatté et indenté, notices en format natif de Docalist.',
                'converter' => 'Docalist\Data\Export\Docalist',
                'exporter' => 'Docalist\Data\Export\Xml',
                'exporter-settings' => [
                    'indent' => 4,
                ],
            ];

            return $formats;
        }, 10);

        // Déclare nos assets
        require_once dirname(__DIR__) . '/assets/register.php';
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
     * Retourne l'objet référence dont l'id est passé en paramètre.
     *
     * Implémentation du filtre 'docalist_databases_get_reference'.
     *
     * @param string $id POST_ID de la référence à charger.
     *
     * @return Type Retourne un objet Reference si une grille a été
     * indiquée ; un tableau contenant les données de la notice sinon.
     *
     * @throws Exception
     */
    public function getReference($id = null)
    {
        is_null($id) && $id = get_the_ID();
        $type = get_post_type($id);

        if (! isset($this->databases[$type])) {
            $msg = __("Le post %s n'est pas une référence docalist (postype=%s)");
            throw new Exception(sprintf($msg, $id, $type));
        }

        $database = $this->databases[$type]; /** @var Database $database */
        return $database->load($id);
    }
}
