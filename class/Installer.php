<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data;

use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;

/**
 * Installation/désinstallation du plugin.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Installer
{
    /**
     * Activation : enregistre les tables prédéfinies.
     */
    public function activate()
    {
        $tableManager = docalist('table-manager'); /* @var TableManager $tableManager */

        // Enregistre les tables prédéfinies
        foreach ($this->getTables() as $name => $table) {
            $table['name'] = $name;
            $table['path'] = strtr($table['path'], '/', DIRECTORY_SEPARATOR);
            $table['lastupdate'] = date_i18n('Y-m-d H:i:s', filemtime($table['path']));
            $tableManager->register(new TableInfo($table));
        }
    }

    /**
     * Désactivation : supprime les tables prédéfinies.
     */
    public function deactivate()
    {
        $tableManager = docalist('table-manager'); /* @var TableManager $tableManager */

        // Supprime les tables prédéfinies
        foreach (array_keys($this->getTables()) as $table) {
            $tableManager->unregister($table);
        }
    }

    /**
     * Retourne la liste des tables prédéfinies.
     *
     * @return array
     */
    protected function getTables()
    {
        return
            $this->getGenericTables() +
            $this->getLanguagesTables() +
            $this->getCountriesTables() +
            $this->getContinentsTables();
    }

    /**
     * Tables génériques (indépendantes ou utilisées par plusieurs entités).
     *
     * @return array
     */
    protected function getGenericTables()
    {
        $dir = DOCALIST_DATA_DIR . '/tables/';

        return [
            'content-type' => [
                'path' => $dir . 'content-type.txt',
                'label' => __('Content - Exemple de table "types de contenus"', 'docalist-data'),
                'format' => 'table',
                'type' => 'content-type',
                'creation' => '2018-01-25 17:02:23',
            ],
            'date-type' => [
                'path' => $dir . 'date-type.txt',
                'label' => __('Date - Exemple de table "types de dates"', 'docalist-data'),
                'format' => 'table',
                'type' => 'date-type',
                'creation' => '2018-02-05 10:52:12',
            ],
            'number-type' => [
                'path' => $dir . 'number-type.txt',
                'label' => __('Number - Exemple de table "types de numéros"', 'docalist-data'),
                'format' => 'table',
                'type' => 'number-type',
                'creation' => '2018-02-05 11:08:59',
            ],
            'figure-type' => [
                'path' => $dir . 'figure-type.txt',
                'label' => __('Figure - Exemple de table "types de chiffres clés"', 'docalist-data'),
                'format' => 'table',
                'type' => 'figure-type',
                'creation' => '2016-02-22 14:57:49',
            ],
            'topic-type' => [
                'path' => $dir . 'topic-type.txt',
                'label' => __('Topic - Exemple de table "types de topics"', 'svb'),
                'format' => 'table',
                'type' => 'topic-type',
                'creation' => '2014-02-05 08:38:11',
            ],
            'phone-number-type' => [
                'path' => $dir . 'phone-number-type.txt',
                'label' => __('Téléphone - Exemple de table "types de numéros de téléphone"', 'docalist-data'),
                'format' => 'table',
                'type' => 'phone-number-type',
                'creation' => '2015-12-16 17:16:19',
            ],
            'postal-address-type' => [
                'path' => $dir . 'postal-address-type.txt',
                'label' => __('Address - Exemple de table "types d\'adresses postales"', 'docalist-data'),
                'format' => 'table',
                'type' => 'postal-address-type',
                'creation' => '2016-01-11 16:31:37',
            ],
            'link-type' => [
                'path' => $dir . 'link-type.txt',
                'label' => __('Link - Exemple de table "types de liens"', 'docalist-data'),
                'format' => 'table',
                'type' => 'link-type',
                'creation' => '2015-12-16 08:15:50',
            ],
            'source-type' => [
                'path' => $dir . 'source-type.txt',
                'label' => __('Source - Exemple de table "types de sources"', 'docalist-data'),
                'format' => 'table',
                'type' => 'source-type',
                'creation' => '2018-05-04 10:32:33',
            ],
        ];
    }

    /**
     * Retourne la liste des tables "langues".
     *
     * @return array
     */
    protected function getLanguagesTables()
    {
        $dir = DOCALIST_DATA_DIR . '/tables/languages/';

        return [
            // Tables des langues complète
            'ISO-639-2_alpha3_fr' => [
                'path' => $dir . 'ISO-639-2_alpha3_fr.txt',
                'label' => __('Langues - Monde entier (codes 3 lettres, libellés en français)', 'docalist-data'),
                'format' => 'table',
                'type' => 'language',
                'creation' => '2014-03-14 10:11:23',
            ],
            'ISO-639-2_alpha3_en' => [
                'path' => $dir . 'ISO-639-2_alpha3_en.txt',
                'label' => __('Langues - Monde entier (codes 3 lettres, libellés en anglais)', 'docalist-data'),
                'format' => 'table',
                'type' => 'language',
                'creation' => '2014-03-14 10:11:43',
            ],

            // Tables des langues simplifiées (langues officielles de l'union européenne)
            'ISO-639-2_alpha3_EU_fr' => [
                'path' => $dir . 'ISO-639-2_alpha3_EU_fr.txt',
                'label' => __("Langues - Union Européenne (codes 3 lettres, libellés en français)", 'docalist-data'),
                'format' => 'table',
                'type' => 'language',
                'creation' => '2014-03-15 09:01:39',
            ],
            'ISO-639-2_alpha3_EU_en' => [
                'path' => $dir . 'ISO-639-2_alpha3_EU_en.txt',
                'label' => __("Langues - Union Européenne (codes 3 lettres, libellés en anglais)", 'docalist-data'),
                'format' => 'table',
                'type' => 'language',
                'creation' => '2014-03-15 09:01:39',
            ],

            // Tables de conversion des codes langues
            'ISO-639-2_alpha2-to-alpha3' => [
                'path' => $dir . 'ISO-639-2_alpha2-to-alpha3.txt',
                'label' => __('Langues - Table de conversion des codes 2 lettres en codes 3 lettres', 'docalist-data'),
                'format' => 'conversion',
                'type' => 'language-conversion',
                'creation' => '2014-03-14 10:12:15',
            ],
        ];
    }

    /**
     * Retourne la liste des tables "pays".
     *
     * @return array
     */
    protected function getCountriesTables()
    {
        $dir = DOCALIST_DATA_DIR . '/tables/countries/';

        return [
            'ISO-3166-1_alpha2_fr' => [
                'path' => $dir . 'ISO-3166-1_alpha2_fr.txt',
                'label' => __('Pays - Monde entier (codes 2 lettres, libellés en français)', 'docalist-data'),
                'format' => 'table',
                'type' => 'country',
                'creation' => '2014-03-14 10:08:17',
            ],
            'ISO-3166-1_alpha2_en' => [
                'path' => $dir . 'ISO-3166-1_alpha2_en.txt',
                'label' => __('Pays - Monde entier (codes 2 lettres, libellés en anglais)', 'docalist-data'),
                'format' => 'table',
                'type' => 'country',
                'creation' => '2014-03-14 10:08:32',
            ],
            'ISO-3166-1_alpha3-to-alpha2' => [
                'path' => $dir . 'ISO-3166-1_alpha3-to-alpha2.txt',
                'label' => __('Pays - Table de conversion des codes 3 lettres en codes 2 lettres', 'docalist-data'),
                'format' => 'conversion',
                'type' => 'country-conversion',
                'creation' => '2014-03-14 10:09:01',
            ],
            'country-to-continent' => [
                'path' => $dir . 'country-to-continent.txt',
                'label' => __('Pays - Table de conversion pays -> continent', 'docalist-data'),
                'format' => 'conversion',
                'type' => 'country-conversion',
                'creation' => '2016-12-11 10:18:03',
            ],
        ];
    }

    /**
     * Retourne la liste des tables "continents".
     *
     * @return array
     */
    protected function getContinentsTables()
    {
        $dir = DOCALIST_DATA_DIR . '/tables/continents/';

        return [
            'continents_fr' => [
                'path' => $dir . 'continents_fr.txt',
                'label' => __('Continents - Liste en français', 'docalist-data'),
                'format' => 'table',
                'type' => 'continent',
                'creation' => '2016-12-11 10:17:48',
            ],
            'continents_en' => [
                'path' => $dir . 'continents_en.txt',
                'label' => __('Continents - Liste en anglais', 'docalist-data'),
                'format' => 'table',
                'type' => 'continent',
                'creation' => '2016-12-11 10:18:03',
            ],
        ];
    }
}
