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

use Docalist\Container\ContainerInterface;
use Docalist\Data\Export\AdminPage\SettingsPage;
use Docalist\Data\Export\Exporter\DocalistJson;
use Docalist\Data\Export\Exporter\DocalistJsonPretty;
use Docalist\Data\Export\Exporter\DocalistXml;
use Docalist\Data\Export\Exporter\DocalistXmlPretty;
use Docalist\Data\Export\Widget\ExportWidget;
use Docalist\Sequences;
use Docalist\Table\TableManager;
use Docalist\Views;
use Docalist\Data\Database;
use Docalist\Data\Type;
use Docalist\Data\Settings\Settings;
use Docalist\Data\Settings\DatabaseSettings;
use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Entity\ContentEntity;
use InvalidArgumentException;
use Docalist\Repository\SettingsRepository;

use function Docalist\deprecated;

/**
 * Plugin de gestion des bases docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistDataPlugin
{
    /**
     * La liste des bases
     *
     * @var Database[]
     */
    protected $databases;

    public function __construct(
        private ContainerInterface $container,
        private Settings $settings,
        /* private AdminDatabases $adminDatabases */
        /* private SettingsPage $settingsPage */
        private ExportWidget $exportWidget
    ) {
        // todo: on ne peut pas injecter AdminDatabases et SettingsPage car elles appelent les fonctions wordpress dès qu'elles sont créées
        // todo: il faut séparer la création et l'installation (register) mais pour ça il faut remanier AdminPage et toutes les classes descendantes
    }

    /**
     * Initialise le plugin.
     */
    public function initialize(): void
    {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-data', false, 'docalist-data/languages');

        // Debug - permet de réinstaller les tables docalist-data
        if (isset($_GET['reinstall-tables']) && $_GET['reinstall-tables'] === 'docalist-data') {
            $installer = new Installer(docalist(TableManager::class));
            echo 'Uninstall docalist-data tables...<br />';
            $installer->deactivate();
            echo 'Reinstall docalist-data tables...<br />';
            $installer->activate();
            echo 'Done.';
            die();
        }

        // Initialise la liste des bases. On le fait dans l'action init car on ne peut pas appeller
        // register_post_type / add_rewrite_tag avant
        add_action('init', function () {
            // Charge la configuration du plugin
            //$this->settings = new Settings(docalist(SettingsRepository::class));

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
            $this->container->get(AdminDatabases::class)->initialize(); // todo: DI
            $this->container->get(SettingsPage::class)->initialize(); // todo: DI
        });

        // Déclare la liste des types définis dans ce plugin
        add_filter('docalist_databases_get_types', function (array $types) {
            return $types + [
                'content' => ContentEntity::class,
            ];
        });

        // Déclare nos assets
        require_once dirname(__DIR__) . '/assets/register.php';

        // Initialise le widget d'export
        add_action('widgets_init', function () {
            register_widget($this->exportWidget);
        });

        // Déclare les exporteurs définis dans ce plugin
        add_filter('docalist_databases_get_export_formats', function (array $formats) {
            return $formats + [
                DocalistJson::getID()       => DocalistJson::class,
                DocalistJsonPretty::getID() => DocalistJsonPretty::class,
                DocalistXml::getID()        => DocalistXml::class,
                DocalistXmlPretty::getID()  => DocalistXmlPretty::class,
            ];
        }, 10);

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
