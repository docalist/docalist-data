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
use Docalist\Schema\Schema;
use Docalist\Forms\Container;
use Docalist\PostalAddressMetadata\PostalAddressMetadata;
use InvalidArgumentException;

/**
 * PostalAddress : un type composite comprenant les différentes informations nécessaires pour envoyer un courrier
 * postal (adresse, code postal, ville, pays...)
 *
 * @property LargeText  $address            Addresse
 * @property Text       $subLocality        Quartier
 * @property Text       $locality           Ville
 * @property Text       $postalCode         Code postal
 * @property Text       $sortingCode        Cedex
 * @property Text[]     $administrativeArea Région/département/etc.
 * @property TableEntry $country            Code pays
 * @property GeoPoint   $location           Localisation (lat/lon)
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
     * - Exemple de formulaire dont les champs s'adaptent selon pays (faire "checkout") :
     *   https://995d1d846ff2ec4fd846220368e80c16471c764d.googledrive.com/host/0B28BnxIvH5DuWGc3Mm5sZE9DekE/
     * - Formattage d'adresse (data from google) :
     *   https://github.com/adamlc/address-format
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
                'locality' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Ville', 'docalist-data'),
                ],
                'postalCode' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Code postal', 'docalist-data'),
                ],
                'sortingCode' => [ // cedex
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Cedex', 'docalist-data'),
                ],
                'administrativeArea' => [
                    'type' => 'Docalist\Type\Text*',
                    'label' => __('Région', 'docalist-data'),
                ],
                'country' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'label' => __('Pays', 'docalist-data'),
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
            'default' => __('Par défaut', 'docalist-data'),
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
        $container = $editor->div()->addClass('line');

        // L'adresse comprend deux zones : la carte et le formulaire
        $container->div()->addClass('zone map-container');
        $address = $container->div()->addClass('zone form-container');

        // On a également un input, initialement caché, qui sert à l'autocomplete
        $container->input()
            ->setAttribute('type', 'search')
            ->addClass('search')
            ->setAttribute('placeholder', 'Rechercher...')
            ->setAttribute('style', 'display:none');

        // Construit le formulaire qui contient les différents champs qui composent l'adresse
        $metadata = new PostalAddressMetadata($this->country());
        foreach($metadata->getAddressStructure() as $fields) {
            $group = $address->div()->addClass('line');
            foreach($fields as $name) {
                $fieldOptions = $this->getFieldOptions($name, $options);
                $field = $this->__get($name)->getEditorForm($fieldOptions);
                switch ($name) {
                    case 'address':
                        $label = $field->getLabel();
                        $field->setAttribute('placeholder', $label)
                              ->setAttribute('rows', 1);
                        break;

                    case 'subLocality':
                        $label = $this->getSubLocalityLabel($metadata->getSubLocalityType());
                        $field->setAttribute('placeholder', $label);
                        break ;

                    case 'locality':
                        $label = $this->getLocalityLabel($metadata->getLocalityType());
                        $field->setAttribute('placeholder', $label);
                        break ;

                    case 'postalCode':
                        $label = $this->getPostalCodeLabel($metadata->getPostalCodeType());
                        $field->setAttribute('placeholder', $label);
                        break ;

                    case 'sortingCode':
                        $label = $field->getLabel();
                        $field->setAttribute('placeholder', $label);
                        break;

                    case 'administrativeArea':
                        $label = $this->getAdministrativeAreaLabel($metadata->getAdministrativeAreaType());
                        $field->setAttribute('placeholder', $label);
                        break ;

                    case 'country':
                        $field->setFirstOption(__('Pays', 'docalist-data'));
                        break;
                }
                $field
                    ->setLabel('-')
                    ->setDescription('-')
                    ->addClass($name);

                $field = $group->div()
                    ->addClass('zone')
                    ->add($field);
            }
        }

        // Ajoute la latitude et la longitude
//        $fieldOptions = $this->getFieldOptions('location', $options);
        $location = $this->__get('location')->getEditorForm(new Schema(['editor' =>'hidden'])); /** @var Container $location */
        $location->setLabel('-')->setDescription('-')->addClass('location')->setAttribute('style', 'display: none');
        $container->add($location);

        wp_styles()->enqueue('docalist-postal-address');
        wp_scripts()->enqueue('docalist-postal-address');

        return $editor;
    }

    /**
     * Retourne le libellé à utiliser pour désigner le type de zone géographique indiqué.
     *
     * @param string $administrativeAreaType Le type de zone géographique recherché.
     *
     * @return string Le libellé correspondant
     */
    protected function getAdministrativeAreaLabel($administrativeAreaType)
    {
        switch($administrativeAreaType) {
            case 'area':
                return __('District', 'docalist-data');

            case 'county':
                return __('Comté', 'docalist-data');

            case 'department':
                return __('Département', 'docalist-data');

            case 'district':
                return __('District', 'docalist-data');

            case 'do_si':
                return __('Do/Si', 'docalist-data');

            case 'emirate':
                return __('Émirat', 'docalist-data');

            case 'island':
                return __('Île', 'docalist-data');

            case 'oblast':
                return __('Oblast', 'docalist-data');

            case 'parish':
                return __('Paroisse', 'docalist-data');

            case 'prefecture':
                return __('Préfecture', 'docalist-data');

            case 'province':
                return __('Province', 'docalist-data');

            case 'state':
                return __('État', 'docalist-data');
        }

        return $administrativeAreaType;
    }

    /**
     * Retourne le libellé à utiliser pour désigner le type "locality" indiqué.
     *
     * @param string $localityType Le type de localité recherché.
     *
     * @return string Le libellé correspondant.
     */
    protected function getLocalityLabel($localityType)
    {
        switch($localityType) {
            case 'city':
                return __('Ville', 'docalist-data');

            case 'district':
                return __('District', 'docalist-data');

            case 'post_town':
                return __('Ville postale', 'docalist-data');

            case 'suburb':
                return __('Banlieue', 'docalist-data');
        }

        return $localityType;
    }

    /**
     * Retourne le libellé à utiliser pour désigner le type de sous-localité indiqué.
     *
     * @param string $subLocalityType Le type de sous-localité recherché.
     *
     * @return string Le libellé correspondant.
     */
    protected function getSubLocalityLabel($subLocalityType)
    {
        switch($subLocalityType) {
            case 'district':
                return __('District', 'docalist-data');

            case 'neighborhood':
                return __('Quartier', 'docalist-data');

            case 'suburb':
                return __('Banlieue', 'docalist-data');

            case 'townland':
                return __('Lieu-dit', 'docalist-data');

            case 'village_township':
                return __('Canton', 'docalist-data');
        }

        return $subLocalityType;
    }


    /**
     * Retourne le libellé à utiliser pour désigner le type de code postal indiqué.
     *
     * @param string Le type de code postal recherché.
     *
     * @return string Le libellé correspondant.
     */
    protected function getPostalCodeLabel($postalCodeType)
    {
        switch($postalCodeType) {
            case 'eircode':
                return __('Eircode', 'docalist-data');

            case 'postal':
                return __('Code postal', 'docalist-data');

            case 'zip':
                return __('Code ZIP', 'docalist-data');

            case 'pin':
                return __('Code PIN', 'docalist-data');
        }

        return $postalCodeType;
    }
}
