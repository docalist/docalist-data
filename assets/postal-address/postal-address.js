// Une bonne partie du code est inspiré de l'exemple google : 
// https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete
(function($, window, document, undefined){

    // Options par défaut
    var defaults = {
        mapOptions : {
            center: {lat: 48.1114428, lng: -1.6810943}, // Rennes centre
            zoom: 10,
            
            // disableDefaultUI: true,
            zoomControl: false,         // boutons +/- permettant d'ajuster le zoom
            mapTypeControl: false,      // toogles "plan", "satellite"...
            scaleControl: false,        // Affiche l'échelle de la carte (1cm = x km) 
            streetViewControl: false,   // Affiche le bonhomme 'pegman' pour passer en mode 'street view' 
            rotateControl: false,       // bouton de rotation, seulement si des images à 45° sont dispos
            
            // scrollwheel : false,
            
            mapTypeId : "roadmap",
            
            _zz:false
            
        }
    };
    
    // Notre classe "PostalAddress"
    function PostalAddress(address, options) {
        this.map = null;
        this.marker = null;
        this.autocomplete = null;
        
        this.options = $.extend(true, {}, defaults, options);

        this.address = $(address);
        this.init();
    }

    // Méthodes de notre classe
    $.extend(PostalAddress.prototype, {
        // Initialise tout
        init : function() {
            this.initMap();
            this.initMarker();
            this.initAutocomplete();
            // this.initGeocoder();
            // this.initDetails();
            // this.initLocation();

            var location = $('.location', this.address)
            var lat = $('.latitude', location).val();
            var lon = $('.longitude', location).val();
            if (lat && lon) {
                this.updateMap(new google.maps.LatLng(lat, lon));
            }
        },
        
        // Initialise la carte Google Maps
        initMap : function() {
            var map = $('.map-container', this.address);
            this.map = new google.maps.Map(map[0], this.options.mapOptions);
        },
        
        // Initialise le pin's
        initMarker : function() {
            this.marker = new google.maps.Marker({
                map: this.map,
                anchorPoint: new google.maps.Point(0, -29),
                draggable: true
            });
            
            var that = this;
            google.maps.event.addListener(this.marker, 'dragend', function(event) {
                that.updateLocation(event.latLng);
            });
        },

        // Initialise l'autocomplete
        initAutocomplete : function() {
            // Récupère le input qui sert à rechercher une adresse
            var search = $('.search', this.address);
            
            // Installe l'autocomplete google
            this.autocomplete = new google.maps.places.Autocomplete(search[0], { types: ['geocode'] });
            this.autocomplete.bindTo('bounds', this.map);
            
            // Empêche que le formulaire soit envoyé si l'utilisateur tape "entrée" pour sélectionner une suggestion
            search.on('keypress', function(event){
                if (event.keyCode === 13) {
                    return false;
                }
            });
            
            this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(search[0]);
            
            google.maps.event.addListenerOnce(this.map, 'tilesloaded', function() {
                search.show(); // initialement le input est caché pour éviter un "flouc"
            });
            
            var that = this;
            this.autocomplete.addListener('place_changed', function() {
                var place = this.getPlace();
                
                if (place.geometry && place.geometry.location) {
                    that.updateMap(place.geometry.location);
                }
                that.updateForm(place);
            });
        },
        
        updateMap : function(location) {
            this.map.setCenter(location);
            this.map.setZoom(14);
            this.marker.setPosition(location);
            this.marker.setTitle(location.lat() + ',' + location.lng());

            google.maps.event.trigger(this.map, 'resize'); // mal centré sinon
        },

        updateLocation: function(latLng) {
            var location = $('.location', this.address)
            
            $('.latitude', location).val(latLng.lat());
            $('.longitude', location).val(latLng.lng());
            
            this.marker.setTitle(latLng.lat() + ',' + latLng.lng());
        },
        
        updateForm : function(place) {
            var data = {};
            
            // Indexe les données de l'adresse par type (le premier type si plusieurs)
            $.each(place.address_components, function(index, component){
              var name = component.types[0];

              $.each(component.types, function(index, name){
                data[name] = component.long_name;
                if (component.short_name !== component.long_name) {
                    data[name + "_short"] = component.short_name;
                }
              });
            });

            /*  107 av h fréville :
                    administrative_area_level_1: "Bretagne"
                    administrative_area_level_2: "Ille-et-Vilaine"
                    country: "France"
                    country_short: "FR"
                    locality: "Rennes"
                    political: "France"
                    political_short: "FR"
                    postal_code: "35200"
                    route: "Avenue Henri Fréville"
                    street_number: "107"
                la pb : 
                    administrative_area_level_1: "Bretagne"
                    administrative_area_level_2: "Ille-et-Vilaine"
                    country: "France"
                    country_short: "FR"
                    locality: "Saint-Gilles"
                    political: "France"
                    political_short: "FR"
                    postal_code: "35590"
                    sublocality: "La Pierre Blanche"
                    sublocality_level_1: "La Pierre Blanche"
                
                Manhattan Avenue, Watford, Royaume-Uni :
                    administrative_area_level_2: "Hertfordshire"
                    country: "Royaume-Uni"
                    country_short: "GB"
                    locality: "Watford"
                    political: "Royaume-Uni"
                    political_short: "GB"
                    postal_code: "WD18"
                    postal_code_prefix: "WD18"
                    postal_town: "Watford"
                    route: "Manhattan Avenue"
                    route_short: "Manhattan Ave"

                589 Princes Highway, Rockdale, Nouvelle Galles du Sud, Australie
                    administrative_area_level_1: "New South Wales"
                    administrative_area_level_1_short: "NSW"
                    country: "Australie"
                    country_short: "AU"
                    locality: "Rockdale"
                    political: "Australie"
                    political_short: "AU"
                    postal_code: "2216"
                    route: "Princes Highway"
                    route_short: "Princes Hwy"
                    street_number: "589"

            */
            
            var $form = $('.form-container', this.address);
            
            var address = '';
            if (data.street_number) address = data.street_number;
            if (data.route) address += (address ? ' ' : '') + data.route; 
            if (data.sublocality) address += (address ? "\n" : '') + data.sublocality; 
                
            $('.address', $form).val(address);
//            $('.subLocality', $form).val(data.sublocality);
            $('.locality', $form).val(data.locality);
            $('.postalCode', $form).val(data.postal_code);
//            $('.sortingCode', $form).val(data.postal_code);
            // admin area
            var country = $('select.country', $form)[0].selectize;
            
            country.addOption({code:data.country_short, label:data.country})
            country.setValue(data.country_short);
            if (place.geometry && place.geometry.location) {
                this.updateLocation(place.geometry.location);
            }
        }
    });

    // Plugin jQuery
    $.fn.postalAddress = function(options) {
        var attribute = 'plugin_postal_address';
        return this.each(function() {
            // Evite d'instancier plusieurs fois le même contrôle
            if (!$.data(this, attribute)) {
                $.data(this, attribute, new PostalAddress(this, options));
            }
        });
    };
    
})( jQuery, window, document );

jQuery(document).ready(function($) {
    $('.postal-address').postalAddress();
});
