<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export;

use Docalist\Data\Export\Settings\ExportSettings;
use Docalist\Data\Export\Exporter;
use Docalist\Data\Database;
use Docalist\Data\RecordIterator;
use Docalist\Search\SearchUrl;
use Docalist\Search\SearchResponse;
use Docalist\Search\QueryDSL;
use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Tokenizer;
use Docalist\Views;
use InvalidArgumentException;
use WP_Query;

/**
 * Service docalist-data-export  : génère des fichiers d'export et des bibliographies.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ExportService
{
    /**
     * Les paramètres du plugin.
     *
     * @var ExportSettings
     */
    protected $settings;

    /**
     * Initialise le plugin.
     */
    public function __construct(ExportSettings $settings)
    {
        // Stocke les paramètres du module d'export
        $this->settings = $settings;

        // Récupère l'ID de la page d'export, terminé si on n'en a pas
        $exportPageID = $this->getExportPageID();
        if (empty($exportPageID)) {
            return;
        }

        // Déclenche l'export quand l'utilisateur accède à la page "export"
        add_action('pre_get_posts', function(WP_Query $query) use ($exportPageID) {
            if ($query->is_main_query() && $query->is_page && $exportPageID === $query->get_queried_object_id()) {
                $this->actionExport();
            }
        });
    }

    /**
     * Retourne l'ID de la page "export" indiquée dans les paramètres du plugin.
     *
     * @return int Retourne l'ID WordPress de la page export ou zéro si l'export n'a pas été paramétré.
     */
    private function getExportPageID(): int
    {
        return $this->settings->exportpage->getPhpValue();
    }

    /**
     * Teste si l'utilisateur en cours a les droits requis pour lancer un export.
     *
     * @return bool
     */
    private function currentUserCanExport(): bool
    {
        return 0 !== $this->getExportLimit();
    }

    /**
     * Retourne le nombre maximum de notices que l'utilisateur en cours peut exporter.
     *
     * @return int
     */
    private function getExportLimit(): int
    {
        $user = wp_get_current_user();
        $roles = is_null($user) ? [] : $user->roles;
        $roles[] = '(anonymous)';
        $limit = 0;
        foreach ($roles as $role) {
            if (isset($this->settings->limit[$role])) {
                $limit = max($limit, $this->settings->limit[$role]->limit->getPhpValue());
            }
        }

        return $limit;
    }

    /**
     * Teste si les résultats de recherche passés en paramètre peuvent être exportés.
     *
     * La méthode retourne true si :
     *
     * - l'administrateur a paramétré la page à utiliser pour l'export,
     * - l'utilisateur en cours a les droits requis pour lancer un export,
     * - les résultats de recherche passés en paramètres ne sont pas vides,
     * - on peut accéder à la SearchUrl qui a généré la recherche.
     *
     * @param SearchResponse $searchResponse Résultats de recherche à exporter.
     *
     * @return bool
     */
    private function canExport(SearchResponse $searchResponse): bool
    {
        $exportPage = $this->getExportPageID();
        if (empty($exportPage)) {
            return false;
        }

        if (! $this->currentUserCanExport()) {
            return false;
        }

        if (0 === $searchResponse->getHitsCount()) {
            return false;
        }

        if (is_null($request = $searchResponse->getSearchRequest()) || is_null($request->getSearchUrl())) {
            return false;
        }

        return true;
    }

    /**
     * Retourne l'url permettant d'exporter les résultats d'une recherche passés en paramètre.
     *
     * @param SearchResponse $searchResponse Résultats de recherche à exporter.
     * @param string $format Optionnel, format d'export souhaité. Si aucun format n'est indiqué, le choix sera
     * proposé à l'utilisateur.
     *
     * @return string Retourne l'url obtenue ou une chaine vide si l'export n'est pas possible (i.e. si canExport()
     * retourne false).
     */
    public function getExportUrl(SearchResponse $searchResponse, string $format = ''): string
    {
        // Vérifie que l'export est possible
        if (! $this->canExport($searchResponse)) {
            return '';
        }

        // Récupère l'url complète de la recherche en cours (y compris les types implicites éventuels)
        $searchUrl = $searchResponse->getSearchRequest()->getSearchUrl(); // not null, vérifié par canExport()
        $types = $searchUrl->getTypes();
        if ($searchUrl->hasFilter('in') || empty($types)) {
            $url = $searchUrl->getUrlForPage(1);
        } else {
            $url = $searchUrl->toggleFilter('in', $types);
        }

        // Extrait la query string
        $pt = strpos($url, '?');
        $queryString = ($pt === false) ? '' : substr($url, $pt);

        // Détermine l'url de la page "export"
        $url = get_permalink($this->getExportPageID()); // not zéro, vérifié par canExport()
        $url .= $queryString;

        // Done
        return $url;
    }

    /**
     *
     * Retourne un entête "Content-Disposition" pour le fichier et la disposition indiqués.
     *
     * @param string $filename  Le nom du fichier.
     * @param string $disposition La disposition souhaitée : "attachment" (par défaut) ou "inline".
     *
     * @return string
     */
    private function getContentDispositionHeader(string $filename, string $disposition = 'attachment'): string
    {
        // Crée une version ascii sans espaces du nom du fichier
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $asciiFilename = implode('-', Tokenizer::tokenize($name)) . '.' . $extension;

        // Génère l'entête
        $header = sprintf('Content-Disposition: %s; filename="%s"', $disposition, $asciiFilename);

        // Indique le nom exact du fichier s'il est différent de la version ascii
        ($filename !== $asciiFilename) && $header.= sprintf("; filename*=utf-8''%s", rawurlencode($filename));

        return $header;
    }

    /**
     * Lance une recherche à partir de l'url passée en paramètre.
     *
     * La réponse retournée contient une agrégation "types" de type "terms" sur les types de notices.
     *
     * @param string $url
     *
     * @return SearchResponse Retourne les résultats de recherche obtenus.
     */
    private function search(string $url): SearchResponse
    {
        // Crée une SearchUrl à partir de l'url indiquée
        $searchUrl = new SearchUrl($url);

        // Génère une SearchRequest à partir de cette SearchUrl
        $request = $searchUrl->getSearchRequest();
        $request->setSize(0);

        // Ajoute une agrégation sur les types de notices
        $agg = new TermsAggregation('type', ['size' => 1000]);
        $agg->setName('types');
        $request->addAggregation($agg->getName(), $agg);

        // Exécute la requête et retourne la SearchResponse obtenue
        return $request->execute(); // TODO : si null ?
    }

    /**
     * Retourne le nom complet de la classe PHP qui implémente le type d'enregistrement passé en paramètre.
     *
     * @param string $type Nom du type (par exemple 'article' ou 'book').
     *
     * @return string Nom de la classe Php (par exemple Docalist\Biblio\Entity\BookEntity).
     */
    private function getClassForType(string $type): string
    {
        if ($type === 'post' || $type === 'page') {
            return 'WP_Post';
        }

        try {
            return Database::getClassForType($type); // Génère une exception si ce n'est pas un type docalist
        } catch (InvalidArgumentException $e) {
            return 'UnknownContentType';
        }
    }

    /**
     * Retourne des informations sur les types d'enregistrements qui figurent dans les résutlats de recherche
     * passés en paramètre.
     *
     * @param SearchResponse $searchResponse Les résultats de recherche à examiner (générée par search()).
     *
     * La SearchResponse doit contenir une agrégation "types" de type "terms" sur le champ "type" qui est utilisée
     * pour récupérer le nombre de réponses par type d'enregistrement.
     *
     * @return array[] Un tableau indexé par nom de type dont chaque élément contient les éléments suivants :
     * - 'type' : le nom du type (article, book, etc.)
     * - 'class' : Nom complet de la classe PHP qui gère le type,
     * - 'label' : le libellé du type,
     * - 'count' : le nombre total de réponses de ce type dans les résultats.
     */
    private function getTypesInfo(SearchResponse $searchResponse): array
    {
        $agg = $searchResponse->getAggregation('types'); /* @var TermsAggregation $agg */
        $typesInfo = [];
        foreach ($agg->getBuckets() as $bucket) {
            $typesInfo[$bucket->key] = [
                'type' => $bucket->key,
                'class' => $this->getClassForType($bucket->key),
                'label' => $agg->getBucketLabel($bucket),
                'count' => $bucket->doc_count,
            ];
        }

        return $typesInfo;
    }

    /**
     * Retourne des informations sur les exporteurs capables d'exporter les types d'enregistrements indiqués.
     *
     * @param array[] $typesInfo Le tableau d'informations sur les types d'enregistrements retourné par getTypesInfo().
     *
     * @return array[] Un tableau indexé par ID contenant des informations sur les exporteurs disponibles :
     * - 'ID' : ID de l'exporteur,
     * - 'class' : Nom complet de la classe PHP de l'exporteur,
     * - 'label' : Libellé de l'exporteur,
     * - 'description' : Description de l'exporteur,
     * - 'supported' : Liste des types d'enregistrements supportés,
     * - 'unsupported' : Liste des types d'enregistrements non supportés,
     * - 'count' : Nombre total de notices qui pourront être exportées.
     */
    private function getExportersInfo(array $typesInfo)
    {
        // Récupère l'ID et le nom de classe PHP des exporters disponibles
        $exporters = apply_filters('docalist_databases_get_export_formats', []);

        // Teste les types qui sont supportés par chaque exporteur
        $result = [];
        foreach ($exporters as $key => $class) {
            $exporter = new $class(); /* @var Exporter $exporter */
            $count = 0;
            $supported = $unsupported = [];
            foreach ($typesInfo as $typeInfo) {
                if ($exporter->supports($typeInfo['class'])) {
                    $supported[$typeInfo['type']] = $typeInfo['label'];
                    $count += $typeInfo['count'];
                } else {
                    $unsupported[$typeInfo['type']] = $typeInfo['label'];
                }
            }
            if ($count !== 0) {
                $result[$key] = [
                    'ID' => $key,
                    'class' => $class,
                    'label' => $exporter->getLabel(),
                    'description' => $exporter->getDescription(),
                    'supported' => $supported,
                    'unsupported' => $unsupported,
                    'count' => $count,
                ];
            }
        }

        // Ok
        return $result;
    }

    /**
     * Lance l'export.
     *
     * La méthode vérifie qu'on a tous les paramètres requis et lance l'export.
     * S'il manque des paramètres ou que quelque chose ne va pas, elle affiche une vue.
     */
    private function actionExport(): void
    {
        // Teste si l'utilisateur en cours a le droit d'exporter et détermine la limite
        $limit = $this->getExportLimit();
        if (0 === $limit) {
            $this->view('docalist-data:export/access-denied');

            return;
        }

        // Lance une recherche à partir des paramètres transmis en query string
        $searchResponse = $this->search($_SERVER['REQUEST_URI']);

        // Affiche un message à l'utilisateur si on n'a aucune réponse
        if ($searchResponse->getHitsCount() === 0) {
            $this->view('docalist-data:export/nohits');

            return;
        }

        // Récupère des informations sur les types d'enregistrements qui figurent dans les résultats
        $typesInfo = $this->getTypesInfo($searchResponse);

        // Récupère des informations sur les exporteurs capables d'exporteurs tout ou partie des résultats
        $exportersInfo = $this->getExportersInfo($typesInfo);

        // Affiche un message à l'utilisateur si aucun exporteur n'est disponible
        if (0 === count($exportersInfo)) {
            $this->view('docalist-data:export/noformat', ['count' => $searchResponse->getHitsCount()]);

            return;
        }

        // Affiche la page "choix du format" si aucun exporteur n'a été indiqué
        if (empty($_GET['_exporter'])) {
            $this->view('docalist-data:export/form', [
                'exportersInfo' => $exportersInfo,
                'count' => $searchResponse->getHitsCount(),
                'max' => $limit,
            ]);

            return;
        }

        // Affiche un message si l'exporteur indiqué n'existe pas ou ne peut pas traiter les données à exporter
        $exporter = $_GET['_exporter'];
        if (!empty($exporter) && !isset($exportersInfo[$exporter])) {
            $this->view('docalist-data:export/invalidformat');

            return;
        }

        // Lance l'export
        $class = $exportersInfo[$exporter]['class'];
        $supportedTypes = array_keys($exportersInfo[$exporter]['supported']);
        unset($exportersInfo);
        $exporter = new $class(); /* @var Exporter $exporter */

        // Génère les entêtes http
        $disposition = $exporter->isBinaryContent() ? 'attachment' : 'inline';
        header('Content-Type: ' . $exporter->getContentType());
        header($this->getContentDispositionHeader($exporter->suggestFilename(), $disposition));
        header('X-Content-Type-Options: nosniff');

        // Permet au script de s'exécuter longtemps
        set_time_limit(3600);

        // Modifie la requête pour qu'elle ne contienne que les types supportés par l'exporteur
        $dsl = docalist('elasticsearch-query-dsl'); /* @var QueryDSL $dsl */
        $request = $searchResponse->getSearchRequest();
        $request->addFilter($dsl->terms('type', $supportedTypes));
        $request->removeAggregation('types'); // l'aggrégation sur les types n'est plus nécessaire

        // Exporte les enregistrements
        set_time_limit(3600);
        $iterator = new RecordIterator($request, $limit);
        $exporter->export($iterator);

        // Stoppe l'exécution de WordPress : on ne veut pas afficher la page "export"
        die();
    }

    /**
     * Remplace le contenu de la page export par le résultat de la vue passée en paramètre.
     *
     * @param string    $view Le nom de la vue à exécuter.
     * @param mixed[]   $data Un tableau contenant les données à transmettre à la vue.
     */
    private function view(string $view, array $data = []): void
    {
        // Exécute la vue
        $views = docalist('views'); /* @var Views $views */
        $data['this'] = $this;
        $content = $views->render($view, $data);

        // On utilise une priorité haute pour court-circuiter les filtres WordPress (wp_autop, embed, etc.)
        $exportPage = $this->getExportPageID();
        add_filter('the_content', function (string $oldContent) use ($content, $exportPage) {
            global $post;

            return (isset($post->ID) && $post->ID === $exportPage) ? $content : $oldContent;
        }, 9999);
    }

    /**
     * Change le titre de la page export.
     *
     * Cette méthode est appellée depuis les vues pour changer le titre de la page.
     *
     * @param string $title Le titre à afficher.
     */
    private function setTitle(string $title): void
    {
        $exportPage = $this->getExportPageID();
        add_filter('the_title', function (string $oldTitle, int $id) use ($title, $exportPage) {
            return ($id === $exportPage) ? $title : $oldTitle;
        }, 9999, 2);
    }
}
