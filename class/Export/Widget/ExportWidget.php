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

namespace Docalist\Data\Export\Widget;

use WP_Widget;
use Docalist\Search\SearchResponse;
use Docalist\Forms\Container;
use Docalist\Search\SearchEngine;
use Docalist\Data\Export\ExportService;

/**
 * Widget "Export Docalist".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ExportWidget extends WP_Widget
{
    public function __construct(private SearchEngine $searchEngine, private ExportService $exportService)
    {
        $id = 'docalist-data-export';
        parent::__construct(
            $id,                                        // ID de base. WordPress ajoute le préfixe "widget"
            __('Export Docalist', 'docalist-data'),     // Titre (nom) du widget affiché en back office
            [                                           // Args
                'description' => __(
                    "Affiche les liens permettant d'exporter la recherche en cours",
                    'docalist-data'
                ),
                'classname' => $id,                     // par défaut, WordPress met 'widget_'.$id
                'customize_selective_refresh' => true,
            ]
        );
    }

    /**
     * Construit l'url permettant d'exporter la recherche en cours.
     *
     * @return string L'url à utiliser pour l'export ou une chaine vide si l'export n'est pas possible.
     */
    private function getExportUrl(): string
    {
        // Récupère les résultats de la recherche en cours
        $searchResponse = $this->searchEngine->getSearchResponse();
        if (is_null($searchResponse)) {
            return '';
        }

        // Ok
        return $this->exportService->getExportUrl($searchResponse);
    }

    /**
     * Affiche le widget.
     *
     * @param array $context Les paramètres d'affichage du widget. Il s'agit des paramètres définis par le thème
     * lors de l'appel à la fonction WordPress.
     *
     * Le tableau passé en paramètre inclut notamment les clés :
     *
     * - before_widget : code html à afficher avant le widget.
     * - after_widget : texte html à affiche après le widget.
     * - before_title : code html à générer avant le titre (exemple : '<h2>')
     * - after_title  : code html à générer après le titre (exemple : '</h2>')
     *
     * @param array $settings Les paramètres du widget que l'administrateur a saisi dans le formulaire de
     * paramétrage (cf. {getSettingsForm()}).
     *
     * @see http://codex.wordpress.org/Function_Reference/register_sidebar
     */
    public function widget($context, $settings)
    {
        // Détermine l'url de l'export
        $url = $this->getExportUrl();
        if (empty($url)) {
            return;
        }

        // TODO: à étudier, avec le widget customizer, on peut être appellé avec des settings vides. Cela se produit
        // quand on ajoute un nouveau widget dans une sidebar, tant qu'on ne modifie aucun paramètre. Dès qu'on
        // modifie l'un des paramètres du widget, celui-ci est correctement enregistré et dès lors on a les settings.
        $settings += $this->getDefaultSettings();

        // Début du widget
        echo $context['before_widget'];

        // Titre du widget
        $title = apply_filters('widget_title', $settings['title'], $settings, $this->id_base);
        if ($title) {
            echo $context['before_title'], $title, $context['after_title'];
        }

        // Début des liens
        $link = '<li class="%s" title="%s"><a href="%s">%s</a></li>';

        echo '<ul>';

        // Lien "Exporter"
        $label = $settings['file'];
        $label && printf(
            $link,
            'export-file',
            __("Génére un fichier d'export", 'docalist-data'),
            $url,
            $label
        );
/*
        // Lien "Bibliographie"
        $label = $settings['print'];
        $label && printf(
            $link,
            'export-print',
            __('Génére une bibliographie', 'docalist-data'),
            $url,
            $label
        );

        // Lien "Mail"
        $label = $settings['mail'];
        $label && printf(
            $link,
            'export-mail',
            __("Génère un fichier d'export et l'envoie par messagerie", 'docalist-data'),
            $url,
            $label
        );
*/
        // Fin des liens
        echo '</ul>';

        // Fin du widget
        echo $context['after_widget'];
    }

    /**
     * Crée le formulaire permettant de paramètrer le widget.
     *
     * @return Container
     */
    protected function getSettingsForm(): Container
    {
        $form = new Container();

        $form->input('title')
            ->setAttribute('id', $this->get_field_id('title'))
            // id pour que le widget affiche le bon titre. cf widgets.dev.js, fonction appendTitle(), L250
            ->setLabel(__('<b>Titre du widget</b>', 'docalist-data'))
            ->addClass('widefat');

        $form->input('file')
            ->setLabel(__('<b>Exporter</b>', 'docalist-data'))
            ->addClass('widefat');
/*
        $form->input('print')
            ->setLabel(__('<b>Créer une bibliographie</b>', 'docalist-data'))
            ->addClass('widefat');

        $form->input('mail')
            ->setLabel(__('<b>Envoyer par messagerie</b>', 'docalist-data'))
            ->addClass('widefat');
*/
        return $form;
    }

    /**
     * Retourne les paramètres par défaut du widget.
     *
     * @return array
     */
    protected function getDefaultSettings()
    {
        return [
            'title' => __('Export', 'docalist-data'),
            'file' => __('Générer un fichier', 'docalist-data'),
            'print' => __('Créer une bibliographie', 'docalist-data'),
            'mail' => __('Envoyer par messagerie', 'docalist-data'),
        ];
    }

    /**
     * Affiche le formulaire qui permet de paramètrer le widget.
     *
     * @see WP_Widget::form()
     */
    public function form($instance)
    {
        // Récupère le formulaire à afficher
        $form = $this->getSettingsForm();

        // Lie le formulaire aux paramètres du widget
        $form->bind($instance ?: $this->getDefaultSettings());

        // Dans WordPress, les widget ont un ID et sont multi-instances. Le formulaire doit donc avoir le même nom
        // que le widget. Par ailleurs, l'API Widgets de WordPress attend des noms de champ de la forme
        // "widget-id_base-[number][champ]". Pour générer cela facilement, on donne directement le bon nom au
        // formulaire. Si on avait des champs répétables, il faudrait définir explicitement repeatLevel=2 dans
        // getSettingsForm().
        $name = 'widget-' . $this->id_base . '[' . $this->number . ']';
        $form->setName($name);

        // Affiche le formulaire
        $form->display();

        return '';
    }

    /**
     * Enregistre les paramètres du widget.
     *
     * La méthode vérifie que les nouveaux paramètres sont valides et retourne les paramètres modifiés.
     *
     * @param array $new les nouveaux paramètres du widget.
     * @param array $old les anciens paramètres du widget
     *
     * @return array La version corrigée des paramètres.
     */
    public function update($new, $old)
    {
        $form = $this->getSettingsForm();
        $form->bind($new);
        $settings = $form->getData();

        // TODO validation

        return $settings;
    }
}
