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
use Docalist\Table\TableInterface;

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

    public static function loadSchema(): array
    {
        return [
            'label' => __('Adresse', 'docalist-data'),

            'fields' => [
                'address' => [
                    'type' => LargeText::class,
                    'label' => __('Adresse', 'docalist-data'),
                ],
                'subLocality' => [
                    'type' => Text::class,
                    'label' => __('Quartier', 'docalist-data'),
                ],
                'postalCode' => [
                    'type' => Text::class,
                    'label' => __('Code postal', 'docalist-data'),
                ],
                'locality' => [
                    'type' => Text::class,
                    'label' => __('Ville', 'docalist-data'),
                ],
                'sortingCode' => [ // cedex
                    'type' => Text::class,
                    'label' => __('Cedex', 'docalist-data'),
                ],
                'administrativeArea' => [
                    'type' => Text::class,
                    'label' => __('État', 'docalist-data'),
                ],
                'country' => [
                    'type' => TableEntry::class,
                    'label' => __('Pays', 'docalist-data'),
                    'description' => false,
                ],
                'administrativeArea2' => [
                    'type' => Text::class,
                    'label' => __('Niveau 2', 'docalist-data'),
                ],
                'administrativeArea3' => [
                    'type' => Text::class,
                    'label' => __('Niveau 3', 'docalist-data'),
                ],
                'administrativeArea4' => [
                    'type' => Text::class,
                    'label' => __('Niveau 4', 'docalist-data'),
                ],
                'administrativeArea5' => [
                    'type' => Text::class,
                    'label' => __('Niveau 5', 'docalist-data'),
                ],
                'location' => [
                    'type' => GeoPoint::class,
                ],
            ]
        ];
    }

    public function assign($value): void
    {
        // 06/02/19 - gère la compatibilité ascendante avec le site svb
        // dans svb, le type PostalAddress avait un champ unique administrativeArea de type tableau de Text
        // désormais, on a des champs différents pour chaque niveau (administrativeArea, administrativeArea2, etc.)
        if (is_array($value) && isset($value['administrativeArea']) && is_array($value['administrativeArea'])) {
            $areas = $value['administrativeArea'];
            $suffixes = ['', '2', '3', '4', '5'];

            foreach ($suffixes as $suffix) {
                unset($value['administrativeArea' . $suffix]);
            }

            foreach ($suffixes as $suffix) {
                if (is_null($area = array_shift($areas))) {
                    break;
                }
                $value['administrativeArea' . $suffix] = $area;
            }
        }

        parent::assign($value);
    }

    public function getFormatSettingsForm()
    {
        $form = parent::getFormatSettingsForm();
        $after = $form->get('after');
        $form->remove($after);

        $form->input('sep')
            ->addClass('small-text')
            ->setLabel(__('Séparateur', 'docalist-data'))
            ->setDescription(
                __("Texte à insérer entre les différents éléments de l'adresse.", 'docalist-data') .
                ' ' .
                __("Laissez vide pour génèrer une adresse sur plusieurs lignes (par défaut).", 'docalist-data')
            );

        $form->checkbox('uppercase')
            ->setLabel(__('Majuscules', 'docalist-data'))
            ->setDescription(__("Mettre certains éléments en majuscules (selon la destination).", 'docalist-data'));

        $form->add($after);

        return $form;
    }

    public function getAvailableFormats()
    {
        return [
            'text' => __('Texte', 'docalist-data'),
            'html' => __('HTML', 'docalist-data'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        $args = [];

        switch ($format) {
            case 'html':
                $args['html'] = true;
                break;

            case 'text':
                $args['html'] = false;
                break;

            default:
                return parent::getFormattedValue($options);
        }

        // Récupère les options d'affichage
        $args['uppercase'] = (bool) $this->getOption('uppercase', $options, false);
        $sep = $this->getOption('sep', $options, '');
        !empty($sep) && $args['separator'] = $sep;

        // Récupère le pays du site (constante DOCALIST_SITE_COUNTRY dans wp-config, 'FR' par défaut)
        $country = defined('DOCALIST_SITE_COUNTRY') ? DOCALIST_SITE_COUNTRY : 'FR';
        $formatter = new PostalAddressMetadata($country);

        // Formatte l'adresse
        return $formatter->format($this->getPhpValue(), $args);

        // Comme les noms de nos champs correspondent aux noms attendus par le formatteur et qu'il ignore les
        // champs en trop (adminarea2, location...), on peut lui passer directement getPhpValue(), ce qui évite
        // de tout recopier dans un tableau intermédiaire.
    }

    public function getAvailableEditors()
    {
        return [
            'default' => __('Par défaut (autocomplete Google Maps API + formualaire + carte)', 'docalist-data'),
        ];
    }

    public function getEditorForm($options = null)
    {
        $editor = $this->getOption('editor', $options, $this->getDefaultEditor());
        switch ($editor) {
            case 'default':
                $form = new Container();
                break;

            default:
                throw new InvalidArgumentException("Invalid PostalAddress editor '$editor'");
        }

        $form
            ->setName($this->schema->name())
            ->setLabel($this->getOption('label', $options))
            ->setDescription($this->getOption('description', $options))
            ->addClass($this->getEditorClass($editor));

        // Chaque adresse est dans une div à part
        $container = $form->div();

        // L'adresse comprend deux lignes : l'autocomplete et une div qui contient la carte et le formulaire
        $container
            ->add($this->editorAutocomplete($options))
            ->add($this->editorMapAndForm($options));

        // Enqueue le JS et la CSS qu'on utilise
        wp_styles()->enqueue('docalist-postal-address');
        wp_scripts()->enqueue('docalist-postal-address');

        // Ok
        return $form;
    }

    /**
     * Construit la partie "autocomplete" de l'éditeur.
     *
     * <div class='type-postal-address-row'>
     *     <input type="search" class="type-postal-address-autocomplete" placeholder="Tapez le début de l'adresse" />
     * </div>
     *
     * @return Div
     */
    protected function editorAutocomplete($options)
    {
        $container = Div::create()->addClass('type-postal-address-row');

        Input::create()
            ->setAttribute('type', 'search')
            ->addClass('type-postal-address-autocomplete')
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
        $container = Div::create()->addClass('type-postal-address-row');

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
        $container = Div::create()->addClass('type-postal-address-col');

        $container->div()->addClass('type-postal-address-map');

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
        $container = Container::create()->addClass('type-postal-address-col type-postal-address-form');

        // Récupère la liste des champs
        $fields = $this->getOption('fields');

        // Ajoute les éditeurs des champs dans le container
        foreach ($fields as $name => $options) {
            $field = $this->__get($name)->getEditorForm($options);
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

    /**
     * Retourne le code du continent correspondant au pays qui figure dans l'adresse.
     *
     * @return string Le code du continent (AF, AN, AS, EU, NA, OC ou SA) ou une chaine vide si le pays est inconnu.
     */
    public function getContinent(): string
    {
        // On ne peut rien faire si on n'a pas le pays
        if (! isset($this->country) || empty($country = $this->country->getPhpValue())) {
            return '';
        }

        // Ouvre la table country-to-continent
        $table = docalist('table-manager')->get('country-to-continent'); /** @var TableInterface $table */

        // Détermine le continent
        return $table->find('dst', 'src=' . $table->quote($country)) ?: '';
    }

    /**
     * Retourne la hiérarchie "continent/pays" de l'adresse.
     *
     * @param string $separator Séparateur à utiliser (slash par défaut).
     *
     * @return string Une chaine de la forme continent/pays.
     */
    public function getContinentAndCountry(string $separator = '/'): string
    {
        // On ne peut rien faire si on n'a pas le pays
        if (! isset($this->country) || empty($country = $this->country->getPhpValue())) {
            return '';
        }

        // Détermine le continent
        return $this->getContinent() . $separator . $country;
    }
}
