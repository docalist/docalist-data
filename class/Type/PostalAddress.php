<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Type;

use Docalist\Type\Composite;
use Docalist\Type\LargeText;
use Docalist\Type\Text;
use Docalist\Type\TableEntry;
use Docalist\Type\GeoPoint;
use Docalist\Forms\Container;
use Docalist\PostalAddressMetadata\PostalAddressMetadata;
use InvalidArgumentException;
use Docalist\Forms\Div;
use Docalist\Forms\Input;

/**
 * PostalAddress : un type composite comprenant les différentes informations nécessaires pour envoyer un courrier
 * postal (adresse, code postal, ville, pays...)
 *
 * @property LargeText  $address                Lignes d'adresse (rue, numéro de rue, lieu-dit).
 * @property Text       $subLocality            Quartier, banlieue, zone résidentielle.
 * @property Text       $postalCode             Code postal.
 * @property Text       $locality               Ville/commune.
 * @property Text       $sortingCode            Clé de tri postal (cedex, boite postale...)
 * @property TableEntry $country                Code ISO du pays.
 * @property Text       $administrativeArea     État, région, province (États-Unis, Canada, Brésil).
 * @property Text       $administrativeArea2    Division administrative de niveau 2 (e.g. département pour la france).
 * @property Text       $administrativeArea3    Division administrative de niveau 3.
 * @property Text       $administrativeArea4    Division administrative de niveau 4.
 * @property Text       $administrativeArea5    Division administrative de niveau 5.
 * @property GeoPoint   $location               Localisation (lat/lon)
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostalAddress extends Composite
{
    /*
     * Ressources :
     * - extensible Address Language (xAL) Ver.2.0 :
     *   https://www.oasis-open.org/committees/ciq/download.shtml
     *   http://xml.coverpages.org/xnal.html
     * - Google LibAddressInput :
     *   https://github.com/googlei18n/libaddressinput
     * - Adressing (adaptation en php de libaddressinput) :
     *   https://github.com/commerceguys/addressing
     * - GeoCoder PHP :
     *   https://github.com/geocoder-php/Geocoder
     * - Address Data (format des adresses, ordres des champs...) :
     *   http://i18napis.appspot.com/address
     * - Doc sur lien précédent :
     *   https://github.com/googlei18n/libaddressinput/wiki/AddressValidationMetadata
     * - International Address Fields in Web Forms :
     *   http://www.uxmatters.com/mt/archives/2008/06/international-address-fields-in-web-forms.php
     * - GPX (GPS eXchange Format) :
     *   https://fr.wikipedia.org/wiki/GPX_(format_de_fichier)
     * - Formattage d'adresse (data from google) :
     *   https://github.com/adamlc/address-format
     */

    /*
     * A voir :
     * - vérifier qu'on a tout ce qu'il faut pour créer la facette hiérarchique
     * - faut-il ajouter des champs supplémentaires pour colloquial_area, premise, subpremise, etc. ?
     */

    public static function loadSchema()
    {
        return [
            'label' => __('Adresse', 'docalist-data'),

            'fields' => [
                'address' => [
                    'type' => 'Docalist\Type\LargeText',
                    'label' => __('Adresse', 'docalist-data'),
                ],
                'subLocality' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Quartier', 'docalist-data'),
                ],
                'postalCode' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Code postal', 'docalist-data'),
                ],
                'locality' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Ville', 'docalist-data'),
                ],
                'sortingCode' => [ // cedex
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Cedex', 'docalist-data'),
                ],
                'administrativeArea' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('État', 'docalist-data'),
                ],
                'country' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'label' => __('Pays', 'docalist-data'),
                    'default' => 'FR',
                    'description' => false,
                ],
                'administrativeArea2' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Niveau 2', 'docalist-data'),
                ],
                'administrativeArea3' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Niveau 3', 'docalist-data'),
                ],
                'administrativeArea4' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Niveau 4', 'docalist-data'),
                ],
                'administrativeArea5' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Niveau 5', 'docalist-data'),
                ],
                'location' => [
                    'type' => 'Docalist\Type\GeoPoint',
                ],
            ]
        ];
    }

    public function format($lineSeparator = ', ', $addCountry = null, $uppercase = false)
    {
        // Récupère le format des adresses pour le pays en cours (ZZ = par défaut)
        $country = isset($this->country) ? $this->country() : 'ZZ';
        $addressFormat = new PostalAddressMetadata($country);

        // Détermine s'il faut afficher ou non le pays et si oui, dans quelle langue
        if (is_null($addCountry)) {
            $addCountry = ($country === $this->guessUserCountry()) ? false : $this->guessUserLanguage();
        }

        // Formatte l'adresse
        return $addressFormat->format($this, $addCountry, $lineSeparator, $uppercase);

        /*
         * Remarque, affichage du pays :
         * 1. On affiche le pays seulement si c'est une adresse à l'étranger
         *    Pour cela, on essaie de deviner le pays de l'utilisateur à partir de l'entête http "accept-language".
         *    Bien sur , ce n'est pas très fiable, mais c'est le moyen le plus simple si on ne veut pas faire de la
         *    géolocalisation à partir de l'adresse IP. Si le pays obtenu est différent du pays qui figure dans
         *    l'adresse (ou si on n'a pas réussi à deviner le pays de l'utilisateur), on affiche le pays.
         * 2. On affiche le nom du pays dans la langue de l'utilisateur
         *    Même principe : on extrait la langue de l'utilisateur à partir de de l'entête http "accept-language".
         *    Si cela ne fonctionne pas, le nom du pays est affiché en anglais.
         */
    }

    public function getAvailableFormats()
    {
        return [
            'default' => __('Par défaut', 'docalist-data'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        switch ($format) {
            case 'default':
                return $this->format();
        }

        throw new InvalidArgumentException("Invalid PostalAddress format '$format'");
    }

    public function getAvailableEditors()
    {
        return [
            'default' => __('Par défaut (autocomplet Google Maps API + formualire + carte)', 'docalist-data'),
        ];
    }

    public function getEditorForm($options = null)
    {
        $editor = $this->getOption('editor', $options, $this->getDefaultEditor());

        switch ($editor) {
            case 'default':
                $editor = new Container();
                break;

            default:
                throw new InvalidArgumentException("Invalid PostalAddress editor '$editor'");
        }

        $editor
            ->setName($this->schema->name())
            ->setLabel($this->getOption('label', $options))
            ->setDescription($this->getOption('description', $options))
            ->addClass('postal-address');

        // Chaque adresse est dans une div à part
        $container = $editor->div()->addClass('postal-address-container');

        // L'adresse comprend deux lignes : l'autocomplete et une div qui contient la carte et le formulaire
        $container
            ->add($this->editorAutocomplete($options))
            ->add($this->editorMapAndForm($options));

        // Enqueue le JS et la CSS qu'on utilise
        wp_styles()->enqueue('docalist-postal-address');
        wp_scripts()->enqueue('docalist-postal-address');

        // Ok
        return $editor;
    }

    /**
     * Construit la partie "autocomplete" de l'éditeur.
     *
     * <div class='postal-address-row'>
     *     <input type="search" class="postal-address-autocomplete" placeholder="Tapez le début de l'adresse" />
     * </div>
     *
     * @return Div
     */
    protected function editorAutocomplete($options)
    {
        $container = Div::create()->addClass('postal-address-row');

        Input::create()
            ->setAttribute('type', 'search')
            ->addClass('postal-address-autocomplete')
            ->setAttribute('placeholder', __(
                "Tapez le début de l'adresse et choisissez dans la liste pour remplir le formulaire.",
                'docalist-data'
            ))
            ->setParent($container);

        return $container;
    }

    /**
     * Construit la partie de l'éditeur qui contient la carte et le formulaire.
     *
     * <div class='postal-address-row'>
     *     <carte>
     *     <formulaire>
     * </div>
     *
     * @return Div
     */
    protected function editorMapAndForm($options)
    {
        $container = Div::create()->addClass('postal-address-row');

        $container->add($this->editorForm($options));
        $container->add($this->editorMap($options));

        return $container;
    }

    /**
     * Construit la partie de l'éditeur qui contient la carte.
     *
     * <div class='postal-address-col'>
     *     <div class="postal-address-map"></div>
     * </div>
     *
     * @return Div
     */
    protected function editorMap($options)
    {
        $container = Div::create()->addClass('postal-address-col');

        $container->div()->addClass('postal-address-map');

        return $container;
    }

    /**
     * Construit la partie de l'éditeur qui contient le formulaire de saisie d'adresse.
     *
     * @return Container
     */
    protected function editorForm($options)
    {
        // Crée le container
        $container = Container::create()->addClass('postal-address-col postal-address-form');

        // Récupère la liste des champs
        $fields = $this->getOption('fields');

        // Ajoute les éditeurs des champs dans le container
        foreach ($fields as $name => $options) {
            $field = $this->__get($name)->getEditorForm($options);
            $field->addClass($name);

            $container->add($field);
        }

        // Ok
        return $container;
    }

    /**
     * Retourne le libellé à utiliser pour le code passé en paramètre.
     *
     * @param string $code Le code recherché (zone géographique, localité, sous-localité, code postal).
     *
     * @return string Le libellé à utiliser.
     */
    protected function getLabel($code)
    {
        // https://github.com/googlei18n/libaddressinput/blob/master/android/src/main/res/values/address_strings.xml
        switch ($code) {
            // administrative area type
            case 'area':
                return _x('District', 'Administrative Area for Hong Kong (e.g. Kowloon)', 'docalist-data');

            case 'county':
                return _x('Comté', 'Administrative Area for the United Kingdom (e.g. Yorkshire)', 'docalist-data');

            case 'department':
                return _x('Département', 'Administrative Area (e.g. Boaco in Nicaragua).', 'docalist-data');

            case 'district':
                return _x('District', 'Administrative Area (e.g. Nauru) or suburb (Korea, China)', 'docalist-data');

            case 'do_si':
                return _x('Do/Si', 'Administrative Area (e.g. Gyeonggi-do or Busan-si in Korea)', 'docalist-data');

            case 'emirate':
                return _x('Émirat', 'Administrative Area for United Arab Emirates (e.g. Abu Dhabi)', 'docalist-data');

            case 'island':
                return _x('Île', 'Administrative Area (e.g. Cat Island in Bahamas).', 'docalist-data');

            case 'oblast':
                return _x('Oblast', 'Administrative Area (e.g. Leningrad in Russia)', 'docalist-data');

            case 'parish':
                return _x('Paroisse', 'Administrative Area (e.g. Canillo in Andorra)', 'docalist-data');

            case 'prefecture':
                return _x('Préfecture', 'Administrative Area (e.g. Hokkaido in Japan)', 'docalist-data');

            case 'province':
                return _x('Province', "Administrative Area (e.g. Ontario in Canada)", 'docalist-data');

            case 'state':
                return _x('État', 'Administrative Area (e.g. California in the USA)', 'docalist-data');

            // locality type
            case 'city':
                return _x('Ville', 'A city or town, such as New York City', 'docalist-data');

            case 'post_town':
                return _x('Ville postale', 'A town which routes postal deliveries (UK addresses)', 'docalist-data');

            case 'suburb':
                return _x('Banlieue', 'Smaller part of a city in some countries (e.g. New Zealand)', 'docalist-data');

            // subLocality type
            case 'neighborhood':
                return _x('Quartier', 'Label for a neighborhood, shown in an address input', 'docalist-data');

            case 'townland':
                return _x('Lieu-dit', 'A division of land in Ireland, shown in an address input', 'docalist-data');

            case 'village_township':
                return _x('Canton', 'A village, township, or precinct in Malaysia', 'docalist-data');

            // postal code type
            case 'eircode':
                return _x('Eircode', 'A code used by the postcode system in Ireland', 'docalist-data');

            case 'postal':
                return _x('Code postal', 'Postal Code used in countries such as Switzerland', 'docalist-data');

            case 'zip':
                return _x('Code ZIP', 'ZIP code used in countries like the US', 'docalist-data');

            case 'pin':
                return _x('Code PIN', 'PIN (Postal Index Number) Code used in India', 'docalist-data');
        }

        return $code;
    }
}
