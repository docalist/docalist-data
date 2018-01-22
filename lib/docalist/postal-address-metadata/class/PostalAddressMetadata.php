<?php
/**
 * This file is part of Docalist Postal Address Metadata.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\PostalAddressMetadata;

use Locale;
use Transliterator;
use InvalidArgumentException;

/**
 * PostalAddressMetadata
 *
 * Fournit des informations sur le format des adresses postales dans tous les pays du monde.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PostalAddressMetadata
{
    /**
     * Path du fichier ressources qui contient les métadonnées sur les adresses.
     *
     * @var string
     */
    const METADATA_PATH = __DIR__ . '/../resources/postal-address-metadata.php';

    /**
     * Le code du pays en cours ('FR' par exemple).
     *
     * @var string
     */
    protected $country;

    /**
     * Les metadonnées pour le pays en cours.
     *
     * Chaque élément du tableau peut contenir les clés suivantes (cf. resources/postal-address-metadata.php) :
     *
     * - fmt :                      une chaine décrivant le format d'affichage (exemple : '%N%n%O%n%A%n%C%n%Z').
     * - locality_name_type :       le type de contenu du champ "locality" (city, district, post_town, suburb..)
     * - require :                  une chaine indiquant les champs qui sont obligatoires (exemple : 'ACSZ').
     * - state_name_type :          le type de contenu du champ "administrativeArea" (comté, district...)
     * - sublocality_name_type :    le type de contenu du champ "subLocality" (quartier, banlieue, canton...)
     * - upper :                    liste des champs qui doivent être en majuscules (exemple : 'CZ').
     * - zip :                      expression régulière à utiliser pour valider le code postal (exemple : '\\d{5}').
     * - zip_name_type :            le type de contenu du champ "postalCode" (code postal, code pin, code zip...)
     * - zipex :                    une chaine contenant des codes postaux valides (exemple : '35000,35590').
     *
     * @var string[]
     */
    protected $metadata;

    /**
     * Codes utilisés pour désigner les champs dans fmt, upper et require.
     *
     * @var string[]
     */
    protected static $codes = [
        'N' => 'recipient',             // Nom de la personne destinataire
        'O' => 'organization',          // Société ou organisme destinataire
        'A' => 'address',               // Adresse
        'D' => 'subLocality',           // Quartier (Dependent locality, champ subLocality)
        'C' => 'locality',              // Ville (City, champ locality)
        'S' => 'administrativeArea',    // Zone administrative (State, champ administrativeArea)
        'Z' => 'postalCode',            // Code postal (Zip, champ postalCode)
        'X' => 'sortingCode',           // Cédex (Sorting code, champ sortingCode)
    ];

    /**
     * Crée un formatter pour le pays indiqué.
     *
     * @param string $country Code à deux lettres du pays en cours (exemple : 'FR') ou une chaine vide
     * pour utiliser un format générique contenant des options par défaut.
     *
     * @throws InvalidArgumentException Si le code pays indiqué n'est pas valide (cf. getCountries).
     */
    public function __construct($country= '')
    {
        $this->setCountry($country);
    }

    /**
     * Retourne la liste des codes pays disponibles.
     *
     * @return string[] Un tableau contenant la liste des pays disponibles (codes ISO à 2 lettres).
     */
    public static function getAvailableCountries()
    {
        $metadata = require self::METADATA_PATH;
        unset($metadata['ZZ']);

        return array_keys($metadata);
    }

    /**
     * Modifie le pays en cours et charge les métadonnées correspondantes.
     *
     * @param string $country Code à deux lettres du pays en cours (exemple : 'FR') ou une chaine vide
     * pour utiliser un format générique contenant des options par défaut.
     *
     * @return self
     *
     * @throws InvalidArgumentException Si le code pays indiqué n'est pas valide (cf. getCountryCodes).
     */
    public function setCountry($country)
    {
        $this->metadata = $this->getMetadataFor(empty($country) ? 'ZZ' : $country);
        $this->country = $country;

        return $this;
    }

    /**
     * Retourne le code du pays en cours.
     *
     * @return string Le code à deux lettres du pays en cours ou une chaine vide (format générique).
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Retourne les métadonnées décrivant le format des adresses postales dans le pays indiqué.
     *
     * @param string $country Code à deux lettres du pays recherché.
     *
     * @return string[] Les métadonnées pour le pays indiqué.
     *
     * @throws InvalidArgumentException Si le code pays indiqué n'est pas valide (cf. getCountryCodes).
     */
    protected function getMetadataFor($country)
    {
        // Charge les métadonnées
        $metadata = require self::METADATA_PATH;

        // Vérifie que le code demandé existe
        if (!isset($metadata[$country])) {
            throw new InvalidArgumentException('No metadata for entry "' . $country . '"');
        }
        $data = $metadata[$country];

        // Applique les valeurs par défaut
        ($country !== 'ZZ') && $data += $metadata['ZZ']; // existe forcément, cf. tests

        // Ok
        return $data;
    }

    /**
     * Convertit un code utilisé par Google en nom de champ.
     *
     * @param string $code le code à convertir (N, O, A, D, C, S, Z ou X).
     *
     * @return string Les nom de champ correspondant.
     *
     * @throws InvalidArgumentException Si le code indiqué est invalide.
     */
    protected static function codeToField($code)
    {
        return self::$codes[$code]; // pas de isset, tous les codes sont validés dans les tests unitaires
    }

    /**
     * Retourne la liste des champs qui composent une adresse postale dans le pays en cours.
     *
     * @return string[]
     */
    public function getUsedFields()
    {
        // Récupère l'option 'fmt' (exemple '%N%n%O%n%A%n%C%n%Z')
        $format = $this->metadata['fmt']; // Pas de isset, existe toujours, cf. tests

        // Extrait tous les codes de la forme %X
        preg_match_all('~%([NOADCSZX])~', $format, $matches);

        // Convertit les codes en noms de champs
        $fields = $matches[1];
        foreach($fields as & $field) {
            $field = $this->codeToField($field);
        }

        // Ajoute le champ pays qui est implicite
        $fields[] = 'country';

        // Ok
        return $fields;
    }

    /**
     * Retourne la liste des champs obligatoires.
     *
     * @return string[] Un tableau contenant les noms de champs correspondants.
     */
    public function getRequiredFields()
    {
        // Récupère l'option 'require' (exemple 'ACZ')
        $require = $this->metadata['require']; // Pas de isset, existe toujours, cf. tests

        // Convertit les codes en noms de champs
        $fields = array_map([$this, 'codeToField'], str_split($require)); // ne peut pas être vide, cf tests

        // Ajoute le champ 'country' qui est obligatoire de façon implicite
        $fields[] = 'country';

        // Ok
        return $fields;
    }

    /**
     * Retourne l'expression régulière à utiliser pour valider le code postal.
     *
     * @return string L'expression régulière ou une chaine vide (pays sans code postal).
     */
    public function getPostalCodePattern()
    {
        return isset($this->metadata['zip']) ? $this->metadata['zip'] : '';
    }

    /**
     * Retourne la liste des champs qui doivent être écrits en majuscules.
     *
     * @return string[] Un tableau contenant les noms de champs correspondants.
     */
    public function getUppercaseFields()
    {
        // Récupère l'option 'require' (exemple 'ACZ')
        $upper = $this->metadata['upper'];  // Pas de isset, existe toujours, cf. tests

        // Convertit les codes en noms de champs
        return $upper ? array_map([$this, 'codeToField'], str_split($upper)) : []; // 'upper' peut être vide
    }

    /**
     * Retourne un code indiquant le type de contenu attendu dans le champ "administrativeArea" pour le pays en cours.
     *
     * @return string Retourne un des codes suivants :
     *
     * - area :         A Hong-Kong, nom utilisé pour désigner les districts.
     * - county :       Comté (utilisé en Angletere, en Iralnde et à Taïwan).
     * - department :   Département (en Colombie et au Nicaragua).
     * - district :     District (république de Nauru).
     * - do_si :        Utilisé en Corée du sud : "do" désigne une province, "si" indique une métropole ou une  ville.
     * - emirate :      Émirat (utilisé dans les Émirats Arabes Unis).
     * - island :       Île (Bahamas, Cap Vert, Kiribati, St-Christophe, Caïman, Polynésie, Seychelles, Tuvalu)
     * - oblast :       Désigne une région en Russie et en Ukraine.
     * - parish :       Une paroisse (utilisé à la Barbade et en Jamaïque).
     * - prefecture :   Préfecture (utilisé au japon).
     * - province :     Province (valeur par défaut).
     * - state :        État.
     */
    public function getAdministrativeAreaType()
    {
        return $this->metadata['state_name_type']; // Pas de isset, existe toujours, cf. tests
    }

    /**
     * Retourne un code indiquant le type de contenu attendu dans le champ "locality" pour le pays en cours.
     *
     * @return string Retourne un des codes suivants :
     *
     * - city :         Ville
     * - district :     District
     * - post_town :    Ville postale (UK)
     * - suburb :       Banlieue
     */
    public function getLocalityType()
    {
        return $this->metadata['locality_name_type']; // Pas de isset, existe toujours, cf. tests
    }

    /**
     * Retourne un code indiquant le type de contenu attendu dans le champ "subLocality" pour le pays en cours.
     *
     * @return string Retourne un des codes suivants :
     *
     * - district :         District
     * - neighborhood :     Quartier
     * - suburb :           Banlieue
     * - townland :         Un lieu-dit ou un quartier (subdivisions des lands/des paroisses en Irlande)
     * - village_township : Canton (en Malaisie)
     */
    public function getSubLocalityType()
    {
        return $this->metadata['sublocality_name_type']; // Pas de isset, existe toujours, cf. tests
    }

    /**
     * Retourne un code indiquant le type de contenu attendu dans le champ "postalCode" pour le pays en cours.
     *
     * @return string Retourne un des codes suivants :
     *
     * - eircode :  Code EIR (utilisé en Irlande depuis Juillet 2015, cf. https://www.eircode.ie/)
     * - pin :      Code PIN ("Postal Index Number", utilisé en Inde).
     * - postal :   Code postal.
     * - zip :      Code ZIP ("Zone Improvment Plan", utilisé aux USA et extensions : Micronésie, Porto Rico...)
     */
    public function getPostalCodeType()
    {
        return $this->metadata['zip_name_type']; // Pas de isset, existe toujours, cf. tests
    }

    /**
     * Retourne un tableau indiquant la structure des adresses et l'ordre des champs dans le pays en cours.
     *
     * @return array[] Retourne un tableau qui contient autant d'éléments qu'il y a de lignes d'adresse.
     * Chaque élément du tableau contient la liste des champs qui figurent sur cette ligne d'adresse.
     *
     * Par exemple pour le code 'FR', la méthode retourne le tableau suivant :
     * <code>
     * [
     *     ['organization'],                            // 1 : organisme destinataire
     *     ['recipient'],                               // 2 : personne destinataire
     *     ['address'],                                 // 3 : adresse
     *     ['postalCode', 'locality', 'sortingCode'],   // 4 : code postal, ville,cédex
     *     ['country],                                  // 5 : pays
     * ]
     * </code>
     */
    public function getAddressStructure()
    {
        // Récupère l'option 'fmt' (exemple '%N%n%O%n%A%n%C%n%Z')
        $format = $this->metadata['fmt']; // Pas de isset, existe toujours, cf. tests

        // Eclate le format d'adresse en lignes
        $lines = [];
        foreach(explode('%n', $format) as $line) {
            // Extrait les champs qui figurent sur cette ligne
            if (preg_match_all('~%([NOADCSZX])~', $line, $matches)) {
                $lines[] = array_map([$this, 'codeToField'], $matches[1]);
            }
        }

        // Ajoute une ligne supplémentaire pour le pays
        $lines[] = ['country'];

        // Ok
        return $lines;
    }

    /**
     * Formatte l'adresse postale passée en paramètre.
     *
     * La méthode considère qu'on veut faire un envoi postal depuis le pays en cours (celui passé au constructeur)
     * vers le pays qui figure dans l'adresse passée en paramètre. Si les pays sont différents, il s'agit d'un envoi
     * à l'étranger et dans ce cas, le nom du pays est ajouté à l'adresse.
     *
     * @param string[] $address L'adresse à formatter, sous la forme d'un tableau contenant les clés suivantes :
     *
     * - recipient :            Personne destinataire (titre, nom, prénom...)
     * - organization :         Organisme destinataire
     * - address :              Adresse (rue, numéro...)
     * - subLocality :          Quartier ou banlieue
     * - locality :             Ville
     * - administrativeArea :   Région, land, état...
     * - postalCode :           Code postal, code ZIP
     * - sortingCode :          Informations de tri postal (cedex, boite postale...)
     *
     * @param string[] $options Options de formattage :
     *
     * - html :                 bool, génére du code html (false par défaut, génère du texte).
     * - uppercase :            bool, met en majuscules les champs qui doivent l'être (false par défaut).
     * - separator :            string, séparateur de fin de ligne (par défaut, texte : "\n", html : '<br />').
     *
     * @return string
     */
    public function format(array $address, array $options = [])
    {
        // Applique les options par défaut
        $sep = isset($options['html']) && $options['html'] ? '<br />' : "\n";
        $options += [
            'html' => false,
            'uppercase' => false,
            'separator' => $sep,
        ];

        // Détermine le pays de destination
        $metadata = $this->metadata;
        $country = null;
        if (!empty($address['country'])) {
            $destination = strtoupper($address['country']);
            if ($destination !== $this->getCountry()) {
                $metadata = $this->getMetadataFor($destination);
                $country = Locale::getDisplayRegion('-' . $destination) ?: $destination;
            }
        }

        // Met en majuscules les champs qui doivent l'être
        if ($options['uppercase']) {
            $transliterator = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; Upper; NFC');
            foreach(str_split($metadata['upper']) as $code) {
                $field = $this->codeToField($code);
                isset($address[$field]) && $address[$field] = $transliterator->transliterate($address[$field]);
            }
            !is_null($country) && $country = $transliterator->transliterate($country);
        }

        // Crée un tableau de remplacement %code => valeur
        $fields = [];
        foreach(self::$codes as $code => $field) {
            $value = '';
            if (isset($address[$field]))
            {
                $value = $address[$field];
                $value = trim($value, " \t-,\n");
                $value = str_replace("\n", '%n', $value);
                $options['html'] && $value = sprintf('<span class="%s">%s</span>', $field, $value);
            }
            $fields['%' . $code] = $value;
        }

        // Formatte l'adresse
        $lines = explode('%n', strtr($metadata['fmt'], $fields));
        $lines = array_map(
            function($line) {
                return trim($line, " \t-,\n");
            },
            $lines
        );
        $lines = array_filter($lines);
        !is_null($country) && $lines[] = $country;

        // Ok
        return implode($options['separator'], $lines);
    }
}
