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

namespace Docalist\Data\GridBuilder;

use Docalist\Type\Entity;
use Docalist\Schema\Schema;
use Docalist\Data\Type\Group;
use Docalist\Data\Record;
use InvalidArgumentException;

/**
 * Une classe utilitaire permettant de créer la grille de saisie par défaut des entités.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class EditGridBuilder
{
    /**
     * Liste des champs de l'entité.
     *
     * @var Schema[] Un tableau de la forme Nom de champ => Schema.
     */
    private $fields;

    /**
     * Liste des champs déjà utilisés dans un groupe quelconque.
     *
     * @var string[] Un tableau de la forme Nom de champ => true.
     */
    private $used;

    /**
     * Numéro du dernier groupe créé.
     *
     * @var int
     */
    private $groupNumber;

    /**
     * La grille en cours de construction.
     *
     * @var array
     */
    private $grid;

    /**
     * Initialise le constructeur de grille pour l'entité dont le nom de classe est passé en paramètre.
     *
     * @param string $entityClass
     */
    public function __construct(string $entityClass)
    {
        if (! is_a($entityClass, Entity::class, true)) {
            throw new InvalidArgumentException(sprintf('Invalid class, "%s" is not an entity', $entityClass));
        }
        $this->fields = $this->getEntityFields($entityClass);
        $this->used = [];
        $this->groupNumber = 0;
        $this->grid = [
            'name' => 'edit',
            'gridtype' => 'edit',
            'label' => __('Formulaire de saisie', 'docalist-data'),
            'fields' => $this->grid,
        ];
    }

    /**
     * Modifie un paramètre de la grille (label, description, stylesheet...)
     *
     * @param string $name  Nom de la propriété à modifier.
     * @param string $value Nouvelle valeur.
     */
    public function setProperty(string $name, string $value): void
    {
        $protected = ['name', 'gridType', 'fields'];
        if (in_array($name, $protected)) {
            throw new InvalidArgumentException(sprintf('Property "%s" can not be changed', $name));
        }

        $this->grid[$name] = $value;
    }

    /**
     * Retourne la liste des champs de l'entité, en supprimant eux qui sont marqués "unused:true".
     *
     * @param string $entityClass
     *
     * @return Schema[]
     */
    private function getEntityFields(string $entityClass): array
    {
        $fields = $entityClass::getDefaultSchema()->getFields(); /** @var Entity $entityClass */
        foreach ($fields as $name => $field) {
            if ($field->unused()) {
                unset($fields[$name]);
            }
        }

        return $fields;
    }

    /**
     * Ajoute un groupe de champs dans la grille.
     *
     * @param string    $label      Libellé du groupe (titre de la metabox).
     * @param string    $fields     Liste des noms des champs à ajouter au groupe, séparés par une virgule
     *                              (exemple : 'title,content,topic').
     * @param string    $state      Etat initial de la metabox. Les valeurs autorisés sont '' (ouvert),
     *                              'collapsed' (replié), 'hidden' (caché).
     */
    public function addGroup(string $label, string $fields, $state = ''): void
    {
        // Vérifie qu'on a un titre
        if (empty($label)) {
            throw new InvalidArgumentException('A group must have a label');
        }

        // Vérifie qu'on a des champs
        $fields = array_map('trim', explode(',', $fields));
        if (empty($fields)) {
            throw new InvalidArgumentException('A group must have at least one field');
        }

        // Vérifie que $state a l'une des valeurs autorisés
        if (! in_array($state, ['', 'collapsed', 'hidden'])) {
            throw new InvalidArgumentException('Invalid group state');
        }

        // Crée le groupe
        $group = [
            'type' => Group::class,
            'label' => $label
        ];
        ($state !== '') && $group['state'] = $state;
        $this->grid['fields']['group' . (++$this->groupNumber)] = $group;

        // Ajoute tous les champs indiqués
        foreach ($fields as $field) {
            // Vérifie que le champ indiqué existe dans le schéma
            if (!isset($this->fields[$field])) {
                throw new InvalidArgumentException(sprintf(
                    'Field "%s" does not exist (or is marked as "unused" in the entity schema)',
                    $field
                ));
            }

            // Vérifie que le champ indiqué n'a pas été déjà utilisé dans un autre groupe
            if (isset($this->used[$field])) {
                throw new InvalidArgumentException(sprintf('Field "%s" is already used in another group', $field));
            }

            // Ok, ajoute le champ dans la grille
            $this->used[$field] = true;
            $this->grid['fields'][$field] = [];
        }
    }

    /**
     * Ajoute plusieurs groupes de champs dans la grille.
     *
     * @param string[] $groups Un tableau de la forme libellé du groupe => liste des champs
     */
    public function addGroups(array $groups): void
    {
        foreach ($groups as $label => $fields) {
            $state = '';
            if (substr($fields, 0, 1) === '-') {
                $state = 'collapsed';
                $fields = ltrim($fields, '-,');
            }
            $this->addGroup($label, $fields, $state);
        }
    }

    /**
     * Initialise les valeurs par défaut des champs de la grille.
     *
     * @param array $defaults Un tableau de la forme Nom de champ => default indiquant la valeur par défaut des champs.
     *
     * @throws InvalidArgumentException
     */
    public function setDefaultValues(array $defaults): void
    {
        foreach ($defaults as $field => $default) {
            // Vérifie que le champ indiqué existe dans la grille
            if (!isset($this->grid['fields'][$field])) {
                throw new InvalidArgumentException(sprintf('Field "%s" does not exist in grid', $field));
            }

            // Stocke la valeur par défaut du champ
            $this->grid['fields'][$field]['default'] = $default;
        }
    }

    /**
     * Vérifie que la grille d'édition contient tous les champs de l'entité.
     *
     * @throws InvalidArgumentException
     */
    private function checkMissingFields(): void
    {
        $missing = array_diff_key($this->fields, $this->used, Record::getDefaultSchema()->getFields());
        if (!empty($missing)) {
            $missing = implode(', ', array_keys($missing));
            throw new InvalidArgumentException(sprintf('Some fields are missing from the edit grid: %s', $missing));
        }
    }

    /**
     * Retourne la grille obtenue.
     *
     * @return array
     */
    public function getGrid(): array
    {
        // Vérifie que tous les champ ont été ajoutés à la grille
        $this->checkMissingFields();

        // Ok
        return $this->grid;
    }
}
