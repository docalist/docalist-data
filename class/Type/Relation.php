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

use Docalist\Schema\Schema;
use Docalist\Type\Integer;
use Docalist\Forms\Element;
use Docalist\Forms\Container;
use Docalist\Forms\EntryPicker;
use Docalist\Data\Record;
use InvalidArgumentException;

/**
 * Gère une relation vers une fiche docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Relation extends Integer
{
    /**
     * L'entité liée.
     *
     * Est à false initialement, initialisée avec un Record ou null par getEntity(), remis à false par assign().
     *
     * @var Record|null|false
     */
    protected $entity = false;

    public static function loadSchema(): array
    {
        return [
            'relfilter' => '*',  // exemple: "+type:event +database:dbevents"
        ];
    }

    public function __construct($value = null, Schema $schema = null)
    {
        parent::__construct($value, $schema);

        // Vérifie que la classe descendante a indiqué un filtre pour les lookups
        if (is_null($schema) || empty($schema->relfilter())) {
            $field = $schema ? ($schema->name() ?: $schema->label()) : '';
            throw new InvalidArgumentException("Property 'relfilter' is required for Relation field '$field'.");
        }
    }

    public static function getClassDefault()
    {
        // On surcharge car la valeur par défut d'un Integer est 0, on utilise null pour indiquer "pas de relation"
        return null;
    }

    public function assign($value): void
    {
        // Efface l'entité mise en cache (cf. getEntity)
        $this->entity = false;

        // Un Integer ne peut pas être à null, par contre pour un type Relation, il faut accepter la valeur null
        // Les valeurs '', 0 et '0' sont également considérées comme null
        if (is_null($value) || $value === '' || $value === '0' || $value === 0) {
            $this->phpValue = null;

            return;
        }

        parent::assign($value);
    }

    public function getSettingsForm(): Container
    {
        // Récupère le formulaire par défaut
        $form = parent::getSettingsForm();

        // Filtre de recherche (relfilter)
        $form->input('relfilter')
            ->addClass('code large-text')
            ->setLabel(__('Filtre de recherche', 'docalist-data'))
            ->setDescription(__(
                'Equation utilisée pour filtrer les suggestions (lookups) en saisie.
                 Exemple : <code>type:mytype</code> ou <code>+type:mytype +database:mybase</code>.',
                'docalist-data'
            ));

        return $form;
    }

    public function getAvailableEditors(): array
    {
        return [
            'lookup' => __('Lookup dynamique', 'docalist-data'),
            'input' => __('Saisie manuelle du POST ID', 'docalist-data'),
        ];
    }

    public function getEditorForm($options = null): Element
    {
        $editor = $this->getOption('editor', $options, $this->getDefaultEditor());
        switch ($editor) {
            case 'lookup':
                $form = new EntryPicker();
                break;

            case 'input':
                return parent::getEditorForm($options);


            default:
                throw new InvalidArgumentException("Invalid Relation editor '$editor'");
        }

        $form->setOptions('search:' . $this->schema->relfilter());

        return $this->configureEditorForm($form, $options);
    }

    public function getAvailableFormats(): array
    {
        return [
            'id'            => __('Post ID', 'docalist-data'),
            'title'         => __('Titre', 'docalist-data'),
            'url'           => __('Permalien', 'docalist-data'),
            'link-id'       => __('Post ID cliquable', 'docalist-data'),
            'link-title'    => __('Titre cliquable', 'docalist-data'),
            'link-url'      => __('Permalien cliquable', 'docalist-data'),
        ];
    }

    public function getDefaultFormat(): string
    {
        return 'link-title';
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        switch ($format) {
            case 'id':
                return $this->phpValue;

            case 'title':
                return get_the_title($this->phpValue);

            case 'url':
                return get_post_permalink($this->phpValue);

            case 'link-id':
                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    esc_attr(get_post_permalink($this->phpValue)),
                    esc_attr(get_the_title($this->phpValue)),
                    esc_html($this->phpValue)
                );

            case 'link-title':
                return sprintf(
                    '<a href="%s">%s</a>',
                    esc_attr(get_post_permalink($this->phpValue)),
                    esc_html(get_the_title($this->phpValue))
                );

            case 'link-url':
                $url = get_post_permalink($this->phpValue);
                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    esc_attr($url),
                    esc_attr(get_the_title($this->phpValue)),
                    esc_html($url)
                );
        }

        throw new InvalidArgumentException("Invalid Relation format '$format'");
    }

    public function filterEmpty(bool $strict = true): bool
    {
        return empty($this->phpValue);
    }

    /**
     * Retourne l'entité indiquée par la relation.
     *
     * Lors du premier appel, l'entité liée est chargée et est mise en cache. Les appels ultérieurs retournent
     * l'entité en cache (i.e. le record n'est chargé qu'une seule fois).
     *
     * Si la valeur de la relation change, le cache est effacé (cf. assign()).
     *
     * @return Record|null L'objet Record correspondant à l'entité liée ou null s'il n'y a pas d'entité liée.
     */
    public function getEntity(): ?Record
    {
        // Charge l'entité lors du premier appel
        if ($this->entity === false) {
            $id = $this->getPhpValue();
            $this->entity = empty($id) ? null : docalist('docalist-data')->getRecord($id);

        }

        return $this->entity;
    }
}
