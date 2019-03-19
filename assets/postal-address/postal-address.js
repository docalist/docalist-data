/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.

 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

// Une bonne partie du code est inspiré de l'exemple Google :
// https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete
(function ($, window, document, undefined) {

    // Options par défaut
    var defaults = {
        mapOptions : {
            center: {                   // Le "centre" du monde (intersection équateur / méridien de Greenwich)
                lat: 0,
                lng: 0,
            },
            zoom: 1,                    // Niveau de zoom par défaut : le monde pour montrer qu'on n'a pas de géoloc
            zoomControl: false,         // Ne pas afficher les boutons +/- permettant d'ajuster le zoom
            mapTypeControl: false,      // Ne pas afficher les toogles "plan" / "satellite"
            scaleControl: false,        // Ne pas afficher l'échelle de la carte (1cm = x km)
            streetViewControl: false,   // Ne pas afficher le bonhomme 'pegman' pour passer en mode 'street view'
            rotateControl: false,       // Ne pas afficher le bouton de rotation (images à 45°)
            mapTypeId : "roadmap"
        }
    };

    // Notre classe "PostalAddress"
    function PostalAddress(address, options)
    {
        this.map = null;
        this.marker = null;
        this.autocomplete = null;

        this.address = $(address);

        this.options = $.extend(true, {}, defaults, options);

        var lat = Number.parseFloat($('input.field-latitude', this.address).val());
        var lon = Number.parseFloat($('input.field-longitude', this.address).val());
        if (lat && lon) {
            this.options.mapOptions.center.lat = lat;
            this.options.mapOptions.center.lng = lon;
            this.options.mapOptions.zoom = 14;
        }

        this.init();
    }

    // Méthodes de notre classe
    $.extend(PostalAddress.prototype, {
        // Initialise tout
        init : function () {
            this.initMap();
            this.initMarker();
            this.initAutocomplete();
        },

        // Initialise la carte Google Maps
        initMap : function () {
            var map = $('.type-postal-address-map', this.address);
            this.map = new google.maps.Map(map[0], this.options.mapOptions);
        },

        // Initialise le pin's
        initMarker : function () {
            this.marker = new google.maps.Marker({
                map: this.map,
                // anchorPoint: this.options.mapOptions.center, // new google.maps.Point(0, -29),
                position: this.options.mapOptions.center,
                draggable: true
            });

            var that = this;
            google.maps.event.addListener(this.marker, 'drag', function (event) { // ou "dragend"
                that.updateLocation(event.latLng);
            });
        },

        // Initialise l'autocomplete
        initAutocomplete : function () {
            // Récupère le input qui sert à rechercher une adresse
            var search = $('.type-postal-address-autocomplete', this.address);

            // Installe l'autocomplete google
            this.autocomplete = new google.maps.places.Autocomplete(search[0], {  }); // geocode
            this.autocomplete.bindTo('bounds', this.map);

            // Empêche que le formulaire soit envoyé si l'utilisateur tape "entrée" pour sélectionner une suggestion
            search.on('keypress', function (event) {
                if (event.keyCode === 13) {
                    return false;
                }
            });

            var that = this;
            this.autocomplete.addListener('place_changed', function () {
                var place = this.getPlace();

                if (place.geometry && place.geometry.location) {
                    that.updateMap(place.geometry.location);
                }
                that.updateForm(place);
                $('.type-postal-address-autocomplete', this.address).val('');
            });
        },

        updateMap : function (location) {
            this.map.setCenter(location);
            this.map.setZoom(14);
            this.marker.setPosition(location);
            this.marker.setTitle(location.lat() + ',' + location.lng());
        },

        updateLocation: function (latLng) {
            var lat = + latLng.lat().toFixed(6); // le '+' sert à l'arrondi : https://stackoverflow.com/a/12830454
            var lng = + latLng.lng().toFixed(6);

            $('input.field-latitude', this.address).val(lat);
            $('input.field-longitude', this.address).val(lng);

            var value = lat + ',' + lng;
            this.marker.setTitle(value);
        },

        updateForm : function (place) {
            var data = {};

            // Indexe les données de l'adresse par type (le premier type si plusieurs)
            $.each(place.address_components, function (index, component) {
                var name = component.types[0];

                $.each(component.types, function (index, name) {
                    data[name] = component.long_name;
                    if (component.short_name !== component.long_name) {
                        data[name + "_short"] = component.short_name;
                    }
                });
            });

            var address = '';
            if (data.street_number) {
                address = data.street_number;
            }
            if (data.route) {
                address += (address ? ' ' : '') + data.route;
            }
//            if (data.sublocality) address += (address ? "\n" : '') + data.sublocality;
            if (data.subpremise) {
                address += (address ? "\n" : '') + data.subpremise;
            }

            if (data.locality && data.postal_town) { // Exemple : Palais de Buckingham
                address += (address ? "\n" : '') + data.locality;
            }

            $('.field-address', this.address).val(address);
            autosize && autosize.update($('.field-address', this.address)[0]);

            $('.field-subLocality', this.address).val(data.sublocality || data.colloquial_area);
            $('.field-postalCode', this.address).val(data.postal_code);
            $('.field-locality', this.address).val(data.postal_town || data.locality);
            $('.field-sortingCode', this.address).val(''); // pas retourné par google places
            $('.field-administrativeArea', this.address).val(data.administrative_area_level_1);

            var country = $('.field-country', this.address)[0].selectize;
            country.addOption({code:data.country_short, label:data.country})
            country.setValue(data.country_short);

            var hierarchy = data.country;
            for (var i=2; i <= 5; i++) {
                var key = 'administrative_area_level_' + i;
                var value = (data[key] === undefined) ? '' : data[key];
                $('.field-administrativeArea' + i, this.address).val(value);
            }

            if (place.geometry && place.geometry.location) {
                this.updateLocation(place.geometry.location);
            }
        }
    });

    // Plugin jQuery
    $.fn.postalAddress = function (options) {
        var attribute = 'plugin_postal_address';
        return this.each(function () {
            // Evite d'instancier plusieurs fois le même contrôle
            if (!$.data(this, attribute)) {
                $.data(this, attribute, new PostalAddress(this, options));
            }
        });
    };

})(jQuery, window, document);

jQuery(document).ready(function ($) {
    $('.type-postal-address').postalAddress();
});
