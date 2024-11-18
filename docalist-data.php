<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Plugin Name: Docalist Data
 * Plugin URI:  https://docalist.org/
 * Description: Docalist: gestion des bases.
 * Version:     4.2.0
 * Author:      Daniel Ménard
 * Author URI:  https://docalist.org/
 * Text Domain: docalist-data
 * Domain Path: /languages
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
declare(strict_types=1);

namespace Docalist\Data;
use Docalist\Autoloader;
use Docalist\Table\TableManager;
use Docalist\Views;

/**
 * Version du plugin.
 */
define('DOCALIST_DATA_VERSION', '4.2.0'); // Garder synchro avec la version indiquée dans l'entête

/**
 * Path absolu du répertoire dans lequel le plugin est installé.
 *
 * Par défaut, on utilise la constante magique __DIR__ qui retourne le path réel du répertoire et résoud les liens
 * symboliques.
 *
 * Si le répertoire du plugin est un lien symbolique, la constante doit être définie manuellement dans le fichier
 * wp_config.php et pointer sur le lien symbolique et non sur le répertoire réel.
 */
!defined('DOCALIST_DATA_DIR') && define('DOCALIST_DATA_DIR', __DIR__);

/**
 * Path absolu du fichier principal du plugin.
 */
define('DOCALIST_DATA', DOCALIST_DATA_DIR . DIRECTORY_SEPARATOR . basename(__FILE__));

/**
 * Url de base du plugin.
 */
define('DOCALIST_DATA_URL', plugins_url('', DOCALIST_DATA));

/**
 * Initialise le plugin.
 */
add_action('plugins_loaded', function () {
    // Auto désactivation si les plugins dont on a besoin ne sont pas activés
    $dependencies = ['DOCALIST_CORE', 'DOCALIST_SEARCH'];
    foreach ($dependencies as $dependency) {
        if (! defined($dependency)) {
            add_action('admin_notices', function () use ($dependency) {
                deactivate_plugins(DOCALIST_DATA);
                unset($_GET['activate']); // empêche wp d'afficher "extension activée"
                printf(
                    '<div class="%s"><p><b>%s</b> has been deactivated because it requires <b>%s</b>.</p></div>',
                    'notice notice-error is-dismissible',
                    'Docalist Data',
                    ucwords(strtolower(strtr($dependency, '_', ' ')))
                );
            });
            return;
        }
    }

    // Ok
    docalist(Autoloader::class)
        ->add(__NAMESPACE__, __DIR__ . '/class')
        ->add(__NAMESPACE__ . '\Tests', __DIR__ . '/tests')
        ->add('Docalist\PostalAddressMetadata', __DIR__ . '/lib/docalist/postal-address-metadata/class');

    docalist(DocalistDataPlugin::class)->initialize();



});

/*
 * Activation du plugin.
 */
register_activation_hook(DOCALIST_DATA, function () {
    // Si docalist-core n'est pas dispo, on ne peut rien faire
    if (defined('DOCALIST_CORE')) {
        // plugins_loaded n'a pas encore été lancé, donc il faut initialiser notre autoloader
        docalist(Autoloader::class)->add(__NAMESPACE__, __DIR__ . '/class');
        (new Installer(docalist(TableManager::class)))->activate();
    }
});

/*
 * Désactivation du plugin.
*/
register_deactivation_hook(DOCALIST_DATA, function () {
    // Si docalist-core n'est pas dispo, on ne peut rien faire
    if (defined('DOCALIST_CORE')) {
        docalist(Autoloader::class)->add(__NAMESPACE__, __DIR__ . '/class');
        (new Installer(docalist(TableManager::class)))->deactivate();
    }
});
