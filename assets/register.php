<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.

 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Data;

// Les scripts suivants ne sont dispos que dans le back-office
add_action('admin_init', function () {
    $base = DOCALIST_DATA_URL;

    // Css pour EditReference (également utilisé dans le paramétrage de la grille de saisie)
    wp_register_style(
        'docalist-data-edit-reference',
        "$base/assets/edit-reference.css",
        ['wp-admin'],
        '160108'
    );

    // Editeur de grille
    wp_register_script(
        'docalist-data-grid-edit',
        "$base/views/grid/edit.js",
        ['jquery', 'jquery-ui-sortable'],
        '20150510',
        true
    );

    wp_register_style(
        'docalist-data-grid-edit',
        "$base/views/grid/edit.css",
        [],
        '20150510'
    );

    // Adresse Postale
    wp_register_style(
        'docalist-postal-address',
        "$base/assets/postal-address/postal-address.css",
        ['wp-admin'],
        '151229'
    );

    $url = 'https://maps.googleapis.com/maps/api/js?libraries=places';
    defined('GOOGLE_PLACES_LANGUAGE') && $url .= '&language='  . rawurlencode(GOOGLE_PLACES_LANGUAGE); // dans wp-config
    defined('GOOGLE_API_KEY') && $url .= '&key=' . rawurlencode(GOOGLE_API_KEY); // dans wp-config
    wp_register_script(
        'google-maps-places',
        $url,
        [],
        null, // Ne pas générer de numéro de version dans l'url du script, google gère lui-même son versionning
        true
    );
    wp_register_script(
        'docalist-postal-address',
        "$base/assets/postal-address/postal-address.js",
        ['jquery', 'google-maps-places'],
        '20151229',
        true
    );
});
