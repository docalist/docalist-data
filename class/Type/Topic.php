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

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Type\Collection;
use Docalist\Data\Type\Collection\TopicCollection;
use Docalist\Type\Any;
use Docalist\Table\TableManager;
use Docalist\Table\TableInterface;
use Docalist\Forms\TopicsInput;
use Docalist\Forms\Element;
use InvalidArgumentException;

/**
 * Une liste de mots-clés d'un certain type.
 *
 * @property TableEntry $type   Type    Vocabulaire.
 * @property Collection $value  Value   Tags.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Topic extends TypedText
{
    public static function loadSchema(): array
    {
        return [
            'label' => __('Indexation', 'docalist-data'),
            'description' => __(
                'Mots-clés, tags et étiquettes permettant de classer le contenu.',
                'docalist-data'
            ),
            'fields' => [
                'type' => [
                    'label' => __('Vocabulaire', 'docalist-data'),
                ],
                'value' => [
                    'repeatable' => true, // Monovalué dans TypedText, on le rend répétable
                    'label' => __('Termes', 'docalist-data'),
                ],
            ],
            'key' => 'type', // La collection indexe les éléments par type de topic
            'editor' => 'integrated',
        ];
    }

    public static function getCollectionClass(): string
    {
        return TopicCollection::class;
    }

    /* ------------------------------------------------------------------------------------------------------------
     * COMPATIBILITE ASCENDANTE 21/06/17 :
     *
     * - le type Topic est devenu un TypedText standard
     * - le sous-champ 'term' s'appelle maintenant 'value'
     *
     * Les méthodes qui suivent sont là uniquement pour assurer la compatibilité ascendante. Elles pourront être
     * supprimées une fois que prisme et svb auront été adaptés.
     * ------------------------------------------------------------------------------------------------------------ */
    public function assign($value): void
    {
        ($value instanceof Any) && $value = $value->getPhpValue();

        if (is_array($value)) {
            foreach (['term' => 'value'] as $oldName => $newName) {
                if (isset($value[$oldName])) {
                    $value[$newName] = $value[$oldName];
                    unset($value[$oldName]);
                }
            }
        }

        parent::assign($value);
    }

    public function __set(string $name, $value): void
    {
        parent::__set($name === 'term' ? 'value' : $name, $value);
    }

    public function __isset(string $name): bool
    {
        return parent::__isset($name === 'term' ? 'value' : $name);
    }

    public function __unset(string $name): void
    {
        parent::__unset($name === 'term' ? 'value' : $name);
    }

    public function __get(string $name): Any
    {
        return parent::__get($name === 'term' ? 'value' : $name);
    }

    public function __call(string $name, array $arguments)
    {
        return parent::__call($name === 'term' ? 'value' : $name, $arguments);
    }

    /* ------------------------------------------------------------------------------------------------------------
     * FIN COMPATIBILITE ASCENDANTE
     * ------------------------------------------------------------------------------------------------------------ */

    public function getAvailableEditors(): array
    {
        return [];
    }

    public function getEditorForm($options = null): Element
    {
        throw new InvalidArgumentException("Encore utilisée ? normallement c'est Topics qui fait le job");

        $editor = new TopicsInput($this->schema->name(), $this->schema->table());

        $editor
            ->setLabel($this->getOption('label', $options))
            ->setDescription($this->getOption('description', $options));

        return $editor;
    }

    public function getAvailableFormats(): array
    {
        return [
            'v'     => 'Mots-clés',
            'V'     => 'Code des mots-clés (i.e. mots-clés en majuscules)',
            't : v' => 'Nom du vocabulaire : Mots-clés',
            't: v'  => 'Nom du vocabulaire: Mots-clés',
            'v (t)' => 'Mots-clés (Nom du vocabulaire)'
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        switch ($format) {
            case 'v':
                return implode(', ', $this->getTermsLabel());

            case 'V':
                return implode(', ', $this->value->getPhpValue());

            case 't : v':
                $format = '%s : %s'; // espace insécable avant le ':'
                break;

            case 't: v':
                $format = '%s: %s';
                break;

            case 'v (t)':
                $format = '%2$s (%1$s)';
                break;

            default:
                throw new InvalidArgumentException("Invalid Topic format '$format'");
        }

        return sprintf(
            $format,
            $this->type->getEntryLabel(),
            implode(', ', $this->getTermsLabel())
        );
    }

    /**
     * Retourne le libellé des termes du topic.
     *
     * La méthode retourne un tableau qui indique, pour chaque code de topic, le libellé correspondant tel
     * qu'il figure dans la table d'autorité associée au champ type du topic.
     *
     * Si un terme n'existe pas dans la table d'autorité, c'est son code qui est retourné comme label.
     *
     * @return array Un tableau de la forme code => libellé.
     */
    public function getTermsLabel()
    {
        // Récupère la liste des termes
        $terms = $this->value->getPhpValue();
        $terms = array_combine($terms, $terms);

        // Récupère le table-manager
        $tables = docalist('table-manager'); /* @var TableManager $tables */

        // Récupère la table qui contient la liste des vocabulaires (dans le schéma du champ type)
        $table = $this->schema->getField('type')->table();
        $tableName = explode(':', $table)[1];
        $table = $tables->get($tableName); /* @var TableInterface $table */

        // Détermine la source qui correspond au type du topic
        $source = $table->find('source', 'code='. $table->quote($this->type()));
        if ($source !== false) { // type qu'on n'a pas dans la table topics
            list($type, $tableName) = explode(':', $source);

            // Si la source est une table, on traduit les termes
            if ($type === 'table' || $type === 'thesaurus') {
                $table = $tables->get($tableName); /* @var TableInterface $table */
                foreach ($terms as & $term) {
                    $result = $table->find('label', 'code=' . $table->quote($term));
                    $result !== false && $term = $result;
                }
            }
        }

        // Ok
        return $terms;
    }
}
