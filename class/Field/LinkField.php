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

namespace Docalist\Data\Field;

use Docalist\Type\MultiField;
use Docalist\Data\Indexable;
use Docalist\Type\TableEntry;
use Docalist\Type\Url;
use Docalist\Type\Text;
use Docalist\Type\DateTime;
use Docalist\Data\Type\Collection\IndexableMultiFieldCollection;
use Docalist\Data\Indexer\LinkFieldIndexer;
use WP_Embed;
use Docalist\Forms\Container;

/**
 * Champ standard "link" : un lien internet (url, uri, e-mail, hashtag...)
 *
 * Ce champ permet de saisir les liens d'une entité..
 *
 * Chaque lien comporte quatre sous-champs :
 * - `type` : type de lien
 * - `url` : uri,
 * - `label` : libellé à afficher pour ce lien.
 * - `date` : date à laquelle le lien a été consulté/vérifié.
 *
 * Le sous-champ type est associé à une table d'autorité qui contient les types de liens disponibles
 * ('table:link-type'par défaut).
 *
 * @property TableEntry     $type   Type de lien.
 * @property Url            $url    Adresse.
 * @property Text           $label  Libellé.
 * @property DateTime       $date   Accédé le.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LinkField extends MultiField implements Indexable
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'link',
            'label' => __('Liens internet', 'docalist-data'),
            'description' => __('Courriel et liens internet : e-mail, url, hashtag...', 'docalist-data'),
            'repeatable' => true,
            'fields' => [
                'type' => [
                    'type' => TableEntry::class,
                    'label' => __('Type', 'docalist-data'),
                    'description' => __('Type de lien', 'docalist-data'),
                    'table' => 'table:link-type',
                ],
                'url' => [
                    'type' => Url::class,
                    'label' => __('Adresse', 'docalist-data'),
                    'description' => __('Url complète du lien', 'docalist-data'),
                ],
                'label' => [
                    'type' => Text::class,
                    'label' => __('Libellé', 'docalist-data'),
                    'description' => __('Texte à afficher', 'docalist-data'),
                ],
                'date' => [
                    'type' => DateTime::class,
                    'label' => __('Accédé le', 'docalist-data'),
                    'description' => __('Date', 'docalist-data'),
                ],
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function assign($value): void
    {
        /*
         * Par le passé, on avait deux-sous champs supplémentaires :
         * - lastcheck (DateTime) : Date de dernière vérification du lien
         * - status (Text) : Statut du lien lors de la dernière vérification
         * Si on nous assigne des données qui comporte ces sous-champs, on les ignore silencieusement.
         */
        if (is_array($value)) {
            unset($value['lastcheck']);
            unset($value['status']);
        }

        parent::assign($value);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCategoryField(): TableEntry
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableMultiFieldCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return LinkFieldIndexer::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultFormat(): string
    {
        return 'link';
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableFormats(): array
    {
        return [
            'url'       => 'Url',
            'label'     => 'Libellé',
            'link'      => 'Libellé cliquable',
            'urllink'   => 'Url cliquable',
            'labellink' => 'Type et libellé cliquable',
            'embed'     => 'Incorporé si possible, libellé cliquable sinon',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatSettingsForm(): Container
    {
        $form = parent::getFormatSettingsForm();

        $form->select('tooltip')
            ->addClass('tooltip regular-text')
            ->setLabel(__("Bulle d'aide du lien", 'docalist-data'))
            ->setDescription(
                __("Contenu de l'attribut title lorsque le champ est affiché sous forme de lien.", 'docalist-data')
            )
            ->setOptions($this->getTooltipOptions())
            ->setFirstOption(__('(par défaut, choisi selon le format d\'affichage )', 'docalist-data'));

        return $form;
    }

    /**
     * Retourne les options disponibles pour la bulle d'aide des liens.
     *
     * @return string[]
     */
    private function getTooltipOptions(): array
    {
        return [
            '-'             => __('Rien', 'docalist-data'),
            'type'          => __('Type de lien', 'docalist-data'),
            'url'           => __('Url du lien', 'docalist-data'),
            'label'         => __('Libellé du lien', 'docalist-data'),
            'date'          => __('Date du lien', 'docalist-data'),
            'label+date'    => __('Date du lien', 'docalist-data'),
        ];
    }

    /**
     * Retourne la bulle d'aide du lien.
     *
     * @param array $options
     *
     * @return string
     */
    private function getTooltip($options = null, string $default = 'date'): string
    {
        switch ($this->getOption('tooltip', $options, $default))
        {
            case '-':
                return '';

            case 'type':
                return $this->formatField('type', $options);

            case 'url':
                return $this->formatUrl($options);

            case 'label':
                return $this->formatLabel($options);

            case 'date':
                if (isset($this->date)) {
                    return sprintf(
                        __('Lien consulté le %s', 'docalist-data'),
                        $this->formatField('date', $options)
                    );
                }

            case 'label+date':
                $title = $this->formatLabel($options);
                if (isset($this->date)) {
                    $title .= sprintf(
                        __(' (lien consulté le %s)', 'docalist-data'),
                        $this->formatField('date', $options)
                    );
                }
                return $title;
        }

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        switch ($format) {
            case 'url':
                return $this->formatUrl($options);

            case 'label':
                return $this->formatLabel($options);

            case 'link':
                return $this->formatLink($options);

            case 'urllink':
                return $this->formatUrlLink($options);

            case 'labellink':
                return $this->formatLabelLink($options);

            case 'embed':
                return $this->formatEmbed($options);
        }

        return parent::getFormattedValue($options);
    }

    /**
     * Format 'url'.
     *
     * Retourne l'url formattée ou une chaine vide si on n'a pas d'url.
     *
     * @param mixed $options
     *
     * @eturn string
     */
    private function formatUrl($options = null): string
    {
        return isset($this->url) ? $this->formatField('url', $options) : '';
    }

    /**
     * Format 'label'.
     *
     * Retourne le libellé formatté si on a un libellé, le type formatté sinon.
     *
     * @param mixed $options
     *
     * @eturn string
     */
    private function formatLabel($options = null): string
    {
        return $this->formatField(isset($this->label) ? 'label' : 'type', $options);
    }

    /**
     * Format 'link'.
     *
     * Retourne un lien cliquable (tag <a>) en utilisant le libellé (ou le type) comme libellé.
     * La date du lien (si dispo) est indiquée sous forme de bulle d'aide.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatLink($options = null): string
    {
        $title = $this->getTooltip($options, 'date');
        if (!empty($title)) {
            $title = sprintf(' title="%s"', esc_attr($title));
        }

        return sprintf(
            '<a class="%s" href="%s"%s>%s</a>',
            esc_attr($this->type->getPhpValue()),
            esc_attr($this->formatUrl($options)),
            $title,
            esc_html($this->formatLabel($options))
        );
    }

    /**
     * Format 'urllink'.
     *
     * Retourne un lien cliquable (tag <a>) en utilisant l'url comme libellé.
     * Le libellé (ou le type si on n'a aucun libellé) et la date du lien (si disponibles) sont
     * indiqués sous forme de bulle d'aide.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatUrlLink($options = null): string
    {
        $title = $this->getTooltip($options, 'label+date');
        if (!empty($title)) {
            $title = sprintf(' title="%s"', esc_attr($title));
        }

        $url = $this->formatUrl($options);
        return sprintf(
            '<a href="%s" title="%s">%s</a>',
            esc_attr($url),
            esc_attr($title),
            esc_html($url)
        );
    }

    /**
     * Format 'labellink'.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatLabelLink($options = null): string
    {
        return $this->formatUrl($options) . ' : ' . $this->formatLabel($options); // insécable avant ':'
    }

    /**
     * Format 'embed'.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatEmbed($options = null): string
    {
        global $wp_embed; /* @var WP_Embed $wp_embed */

        $url = $this->formatField('url', $options);

        $sav = $wp_embed->return_false_on_fail;
        $wp_embed->return_false_on_fail = true;
        $result = $wp_embed->shortcode(['width' => '480', 'height' => '270'], $url);
        $wp_embed->return_false_on_fail = $sav;

        return $result ?: $this->formatLink($options);

        // Remarque : ce serait plus simple d'utiliser wp_oembed_get($url) mais ça ne gère que les
        // sites qui gèrent oEmbed, pas les providers enregistrés avec wp_embed_register_handler().
    }

    /**
     * {@inheritDoc}
     */
    public function filterEmpty(bool $strict = true): bool
    {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas d'url
        return $this->filterEmptyProperty('url');
    }
}
