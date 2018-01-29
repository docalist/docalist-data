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
    $url = DOCALIST_DATA_URL;

    // Css pour EditReference (également utilisé dans le paramétrage de la grille de saisie)
    wp_register_style(
        'docalist-data-edit-reference',
        "$url/assets/edit-reference.css",
        ['wp-admin'],
        '160108'
    );

    // Editeur de grille
    wp_register_script(
        'docalist-data-grid-edit',
        "$url/views/grid/edit.js",
        ['jquery', 'jquery-ui-sortable'],
        '20150510',
        true
    );

    wp_register_style(
        'docalist-data-grid-edit',
        "$url/views/grid/edit.css",
        [],
        '20150510'
    );

    // Adresse Postale
    wp_register_style(
        'docalist-postal-address',
        "$url/assets/postal-address/postal-address.css",
        ['wp-admin'],
        '151229'
    );
    wp_register_script(
        'google-maps-places',
        '//maps.googleapis.com/maps/api/js?libraries=places',
        [],
        '20151229',
        true
    );
    wp_register_script(
        'docalist-postal-address',
        "$url/assets/postal-address/postal-address.js",
        ['jquery', 'google-maps-places'],
        '20151229',
        true
    );

});
