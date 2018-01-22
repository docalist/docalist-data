<?php
/**
 * This file is part of Docalist Postal Address Metadata.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\PostalAddressMetadata\Scripts;

/**
 * Génère le fichier de ressources postal-address-metadata.php qui est utilisé par la classe
 * PostalAddressMetadata pour gérer l'affichage et la saisie des adresses postales.
 *
 * (cf. https://github.com/googlei18n/libaddressinput/wiki/AddressValidationMetadata)
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostalAddressScriptHelper
{
    /*
     * Les données Google contiennent les champs suivants (exemples pour la france) :
     * - key : code pays ("FR")
     * - id : id du dataset ("data/FR")
     * - name : pays ("FRANCE")
     * - upper : champs à transformer en maju ("CX" = Ville et cedex)
     * - zipex : exemples de codes postaux valides ("33380,34092,33506")
     * - zip : regexp pour valider un code postal ("\d{2}[ ]?\d{3}")
     * - fmt : chaine de formattage ("%O%n%N%n%A%n%Z %C %X")
     * - require : champs obligatoires ("ACZ" = adresse, ville, code postal)
     * - posturl : url du service des postes pour ce pays ("http://www.laposte.fr/")
     * - state_name_type: indique à quoi correspond le découpage administratif (état, province, île...)
     *
     * Codes utilisés pour désigner les champs (utilisés dans upper, fmt et require) :
     * - N : Name
     * - O : Organisation
     * - A : Street Address Line(s)
     * - D : Dependent locality (may be an inner-city district or a suburb)
     * - C : City or Locality
     * - S : Administrative area such as a state, province, island etc
     * - Z : Zip or postal code
     * - X : Sorting code
     *
     * Le champ state_name_type peut contenir les valeurs suivantes :
     * - area (1) : Zone (Hong Kong uniquement, traduire plutôt par "district")
     * - county (3) : Comté (angleterre, irlande, taïwan)
     * - department (2) : Département (Colombie, Nicaragua)
     * - district (1) : District (Nauru)
     * - do_si (1) : Province / ville (Corée du sud, "do" désigne des provinces, "si" désigne des métropoles)
     * - emirate (1) Émirat (émirats arabes)
     * - island (8) : Île
     * - oblast (2) Oblast (une sorte de région en Russie et en Ukraine https://fr.wikipedia.org/wiki/Oblast)
     * - parish (2) : Paroisse (Barbade et Jamaïque)
     * - prefecture (1) : Préfecture (Japon)
     * - province (211) : Province (valeur par défaut)
     * - state (15) : État
     *
     * Le champ localityType (locality_name_type) peut contenir les valeurs suivantes :
     * - city (246) : ville
     * - district (2) : district
     * - post_town (1) : ville postale (UK)
     *
     * Le champ subLocalityType (sublocality_name_type) peut contenir les valeurs suivantes :
     * - district (2) : district
     * - neighborhood (3) : quartier
     * - suburb (243) : banlieue
     * - village_township (1) : canton
     *
     * Le champ postalCodeType (zip_name_type) peut contenir les valeurs suivantes :
     * - postal	(238) : code postal makoritairement utilisé
     * - zip (10) : utilisé aux USA (et extensions : Micronésie, Porto Rico...). "Zone Improvment Plan"
     * - pin (1) : utilisé en Inde, "Postal Index Number".
     */

    /**
     * Url du servide google 'i18napis'.
     *
     * @var string
     */
    const API = 'https://chromium-i18n.appspot.com/ssl-address';

    /**
     * Path du cache.
     *
     * @var string
     */
    protected $cacheDirectory = false;

    /**
     * Définit le répertoire utilisé pour stocker les fichiers téléchargés et active le cache.
     *
     * @param string $cacheDirectory
     */
    public function setCacheDirectory($cacheDirectory)
    {
        $this->cacheDirectory = realpath($cacheDirectory);

        return $this;
    }

    /**
     * Charge les données pour l'id indiqué.
     *
     * @param string $id ID des données à récupérer : 'data', 'data/FR', 'data/ZZ', etc.
     *
     * @eturn array
     */
    protected function download($id)
    {
        // Détermine le nom du fichier
        $file = strtr($id, ['/' => '-']) . '.json';

        // Teste si on a déjà le fichier dans le cache
        $path = null;
        if ($this->cacheDirectory && file_exists($path = $this->cacheDirectory . DIRECTORY_SEPARATOR . $file)) {
            $data = file_get_contents($path);
            $data = json_decode($data, true);

            return $data;
        }

        // Récupère le fichier depuis l'API Google
        $data = file_get_contents(self::API . '/' . $id, false, stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]));

        // Stocke le fichier en cache
        $this->cacheDirectory && file_put_contents($path, $data);

        // Décode les données JSON
        $data = json_decode($data, true);

        // OK
        return $data;
    }

    /**
     * Charge la liste des codes pays pour lesquels google a des données.
     *
     * @return array un tableau de codes pays ISO alpha-2.
     */
    public function getCountries()
    {
        $data = $this->download('data');

        /*
         * On obtient une réponse de la forme :
         * {
         *     "id": "data",
         *     "countries": "AC~AD~...~ZW"
         * }
         */

        return explode('~', $data['countries']);
    }

    /**
     * Charge les données disponibles pour le pays indiqué.
     *
     * @param string $country
     *
     * @return array
     */
    public function getDataFor($country)
    {
        $data = $this->download('data/' . $country);

        // Liste des champs à conserver
        $keep = array_flip([
            'fmt',
            'require',
            'zip',
            'zipex',
            'upper',
            'state_name_type',
            'locality_name_type',
            'sublocality_name_type',
            'zip_name_type'
        ]);

        // Supprime les champs qu'on ne veut pas conserver
        $data = array_intersect_key($data, $keep);

        // Trie les champs pour qu'ils soient toujours dans le même ordre
        ksort($data);

        // Ok
        return $data;
    }

    /**
     * Corrige les données google.
     *
     * Adapté de :
     * https://github.com/commerceguys/addressing/blob/master/resources/library_customizations.php
     *
     * @param array $data
     */
    public function applyCorrections(array $data)
    {
        // Code postal obligatoire, région manquate (EE, LV, LT).
        // https://github.com/googlei18n/libaddressinput/issues/64
        foreach(['EE', 'LT'] as $country) {
            $data[$country]['require'] = 'ACZ';
            $data[$country]['fmt']   .= ' %S';
            $data[$country]['state_name_type']  = 'county';
        }

        $data['LV']['require'] = 'ACZ';


        // Code postal obligatoire (CZ and SK).
        // https://github.com/googlei18n/libaddressinput/issues/88
        $data['CZ']['require'] = $data['SK']['require'] = 'ACZ';

        return $data;
    }

    public function getDefault()
    {
        // Les valeurs par défaut sont stockées dans l'entrée "ZZ"
        $default = $this->getDataFor('ZZ');

        // Modifie le format pour être sur que tous les champs sont affichés par défaut
        $default['fmt'] = '%O%n%N%n%A%n%D%n%Z %C %X%n%S';

        // Ok
        return $default;
    }

    public function prettyVarExport($var, $indent="")
    {
        switch (gettype($var)) {
            case 'string':
                return "'" . addcslashes($var, "\\\$\'\r\n\t\v\f") . "'";

            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                    . ($indexed ? "" : $this->prettyVarExport($key) . " => ")
                    . $this->prettyVarExport($value, "$indent    ");
                }
                return '[' . PHP_EOL . implode(',' . PHP_EOL, $r) . PHP_EOL . $indent . "]";

            case 'boolean':
                return $var ? "TRUE" : "FALSE";

            default:
                return var_export($var, TRUE);
        }
    }
}
