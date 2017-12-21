<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Plugin Name: Docalist Databases
 * Plugin URI:  http://docalist.org/
 * Description: Docalist: gestion des bases.
 * Version:     0.16.0
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Text Domain: docalist-databases
 * Domain Path: /languages
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases;

/**
 * Version du plugin.
 */
define('DOCALIST_DATABASES_VERSION', '0.16.0'); // Garder synchro avec la version indiquée dans l'entête

/**
 * Path absolu du répertoire dans lequel le plugin est installé.
 *
 * Par défaut, on utilise la constante magique __DIR__ qui retourne le path réel du répertoire et résoud les liens
 * symboliques.
 *
 * Si le répertoire du plugin est un lien symbolique, la constante doit être définie manuellement dans le fichier
 * wp_config.php et pointer sur le lien symbolique et non sur le répertoire réel.
 */
!defined('DOCALIST_DATABASES_DIR') && define('DOCALIST_DATABASES_DIR', __DIR__);

/**
 * Path absolu du fichier principal du plugin.
 */
define('DOCALIST_DATABASES', DOCALIST_DATABASES_DIR . DIRECTORY_SEPARATOR . basename(__FILE__));

/**
 * Url de base du plugin.
 */
define('DOCALIST_DATABASES_URL', plugins_url('', DOCALIST_DATABASES));

/**
 * Initialise le plugin.
 */
add_action('plugins_loaded', function () {
    // Auto désactivation si les plugins dont on a besoin ne sont pas activés
    $dependencies = ['DOCALIST_CORE', 'DOCALIST_SEARCH'];
    foreach ($dependencies as $dependency) {
        if (! defined($dependency)) {
            return add_action('admin_notices', function () use ($dependency) {
                deactivate_plugins(DOCALIST_DATABASES);
                unset($_GET['activate']); // empêche wp d'afficher "extension activée"
                printf(
                    '<div class="%s"><p><b>%s</b> has been deactivated because it requires <b>%s</b>.</p></div>',
                    'notice notice-error is-dismissible',
                    'Docalist Databases',
                    ucwords(strtolower(strtr($dependency, '_', ' ')))
                );
            });
        }
    }

    // Ok
    docalist('autoloader')
        ->add(__NAMESPACE__, __DIR__ . '/class')
        ->add(__NAMESPACE__ . '\Tests', __DIR__ . '/tests');

    docalist('services')->add('docalist-databases', new Plugin());
});

/*
 * Activation du plugin.
 */
    register_activation_hook(DOCALIST_DATABASES, function () {
    // Si docalist-core n'est pas dispo, on ne peut rien faire
    if (defined('DOCALIST_CORE')) {
        // plugins_loaded n'a pas encore été lancé, donc il faut initialiser notre autoloader
        docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
        (new Installer())->activate();
    }
});

/*
 * Désactivation du plugin.
*/
register_deactivation_hook(DOCALIST_DATABASES, function () {
    // Si docalist-core n'est pas dispo, on ne peut rien faire
    if (defined('DOCALIST_CORE')) {
        docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
        (new Installer())->deactivate();
    }
});
