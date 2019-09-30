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

namespace Docalist\Data\Indexer;

use Docalist\Data\Indexer;
use Docalist\Type\Any;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Transliterator;
use InvalidArgumentException;

/**
 * Classe de base pour les indexeurs des champs.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class FieldIndexer implements Indexer
{
    /**
     * Champ à indexer.
     *
     * @var Any
     */
    protected $field;

    /**
     * Liste des attributs de recherche actif (initialisé par initActiveAttributes).
     *
     * @var array
     */
    protected $attributes;

    /**
     * Liste des codes d'attribut.
     *
     * Ce tableau liste tous les codes d'attributs qu'on peut passer à getAttributeName(), getAttributeLabel()
     * et getAttributeDescription().
     *
     * Pour chaque attribut, il indique le format de l'attribut qui sera généré dans le mapping.
     * Le premier format est utilisé pour les attributs génériques, le second format est utilisé pour
     * les attributs spécifiques à un type.
     */
    private const ATTRIBUTES = [
        'search'            => ['%s',                   '%s-%s'                 ],

        'filter'            => ['filter.%s',            'filter.%s-%s'          ],
        'code-filter'       => ['filter.%s.code',       'filter.%s-%s.code'     ],
        'label-filter'      => ['filter.%s.label',      'filter.%s-%s.label'    ],

        'suggest'           => ['suggest.%s',           'suggest.%s-%s'         ],
        'code-suggest'      => ['suggest.%s.code',      'suggest.%s-%s.code'    ],
        'label-suggest'     => ['suggest.%s.label',     'suggest.%s-%s.label'   ],

        'hierarchy'         => ['hierarchy.%s',         'hierarchy.%s-%s'       ],
        'code-hierarchy'    => ['hierarchy.%s.code',    'hierarchy.%s-%s.code'  ],
        'label-hierarchy'   => ['hierarchy.%s.label',   'hierarchy.%s-%s.label' ],

        'sort'              => ['sort.%s',              'sort.%s-%s'            ],
        'code-sort'         => ['sort.%s.code',         'sort.%s-%s.code'       ],
        'label-sort'        => ['sort.%s.label',        'sort.%s-%s.label'      ],
    ];

    /**
     * Initialise l'indexeur pour le champ passé en paramètre.
     *
     * @param Any $field
     */
    public function __construct(Any $field)
    {
        $this->field = $field;
        $this->initAttributes();
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexSettingsForm(): Container
    {
        return (new Container('index'))
            ->setLabel(__('Indexation', 'docalist-data'))
            ->setDescription(__(
                'Attributs de recherche à générer lorsque ce champ est indexé dans docalist-search.',
                'docalist-data'
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(): Mapping
    {
        return new Mapping($this->getFieldName());
    }

    /**
     * Retourne les options d'indexation du champ.
     *
     * @return array
     */
    final protected function getOptions(): array
    {
        return (array) $this->field->getSchema()->__call('index');
    }

    /**
     * Teste si une option d'indexation est active.
     *
     * @param string $name Nom de l'option à tester.
     *
     * @return bool
     */
    final protected function hasOption(string $name): bool
    {
        if (isset(self::ATTRIBUTES[$name])) {
            return !empty($this->getOptions()[$name]);
        }

        throw new InvalidArgumentException(sprintf('Invalid attribute %s', $name));
    }

    /**
     * Retourne la valeur d'une option d'indexation.
     *
     * @param string $name Nom de l'option à retourner.
     *
     * @return mixed
     */
    final protected function getOption(string $name)
    {
        return $this->getOptions()[$name];
    }

    /**
     * Teste si le champ est indexé.
     *
     * @return bool Retourne true si au moins l'une des options d'indexation du champ est activée.
     */
    final protected function isIndexed(): bool
    {
        return !empty($this->getOptions());
    }


    /**
     * Retourne le nom du champ à indexer.
     *
     * @return string
     */
    final protected function getFieldName(): string
    {
        return $this->field->getSchema()->name();
    }

    /**
     * Génère le nom d'un attribut de recherche.
     *
     * @param string $attribute Nom de code de l'attribut à générer (une des clés de la constante ATTRIBUTES).
     * @param string $type      Type optionnel.
     *
     * @return string
     */
    final protected function getAttributeName(string $attribute, string $type = ''): string
    {
        if (isset(self::ATTRIBUTES[$attribute])) {
            return
                empty($type)
                ? sprintf(self::ATTRIBUTES[$attribute][0], $this->getFieldName())
                : sprintf(self::ATTRIBUTES[$attribute][1], $this->getFieldName(), $type);
        }

        throw new InvalidArgumentException(sprintf('Invalid attribute %s', $attribute));
    }

    /**
     * Retourne la liste des attributs générés par le champ, selon les options d'indexation choisies.
     *
     * @return array Retourne un tableau qui indique les noms des attributs de recherche à générer pour chacune
     * des options d'indexation activées.
     *
     * Pour les options simples (search, filter...), la valeur associée contient le nom de l'attribut à générer.
     *
     * Pour les options typées (search-types, filter-types...), la valeur associée contient un tableau
     * de la forme "type => nom de l'attribut à générer" pour chacun des types choisis par l'utilisateur.
     *
     * Les options d'indexation qui ne sont pas activées ne figurent pas dans le tableau retourné.
     *
     * Exemple pour un champ "number" :
     * [
     *     'search': 'number',
     *     'search-types' => [
     *         'issn' => 'number-issn',
     *         'isbn' => 'number-isbn',
     *     ]
     *    'filter-types' => [
     *         'issn' => 'filter.number.issn',
     *         'isbn' => 'filter.number.isbn',
     *     ]
     * ]
     */
    final protected function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retourne le libellé de l'attribut de recherche indiqué en paramètre.
     *
     * @param string $attribute Nom de code de l'attribut à générer.
     * @param string $type      Type optionnel.
     *
     * @return string
     */
    protected function getAttributeLabel(string $attribute, string $type = ''): string
    {
        throw new InvalidArgumentException(sprintf('Missing label for attribute %s', $attribute));
    }

    /**
     * Retourne la description de l'attribut de recherche indiqué en paramètre.
     *
     * @param string $attribute Nom de code de l'attribut à générer (une des clés de la constante ATTRIBUTES).
     * @param string $type      Type optionnel.
     *
     * @return string
     */
    protected function getAttributeDescription(string $attribute, string $type = ''): string
    {
        throw new InvalidArgumentException(sprintf('Missing description for attribute %s', $attribute));
    }

    /**
     * Initialise la propriété attributes (liste des attributs actifs).
     *
     * @return array
     */
    protected function initAttributes(): void
    {
        // Récupère les options d'indexation du champ
        $options = $this->getOptions();

        // Génère le nom des attributs de recherche correspondant aux options activées
        $this->attributes = [];
        foreach (array_keys(self::ATTRIBUTES) as $attribute) {
            // Option simple (search, filter...) : booléen qui indique si l'option est active
            isset($options[$attribute]) && $this->attributes[$attribute] = $this->getAttributeName($attribute);

            // Option typée (search-types, filter-types...) : types pour lesquels on a un champ spécifique
            $option = $attribute . '-types';
            if (isset($options[$option])) {
                foreach ((array) $options[$option] as $type) {
                    $this->attributes[$option][$type] = $this->getAttributeName($attribute, $type);
                }
            }
        }
    }

    /**
     * Retourne un tableau d'options utilisable dans un select pour la liste de type passée en paramètre.
     *
     * Cette méthode est utilisée dans les options d'indexation pour générer une liste de types pour
     * lesquels l'utilisateur peut créer des attributs de recherche sépcifiques.
     *
     * Exemple pour un champ topic :
     *
     * prepareSelect('search', ['prisme' => 'Mots-clés prisme', 'free' => 'Mots-clés libres'])
     * -> ['prisme' => 'Mots-clés prisme (topic-prisme)', 'free' => 'Mots-clés libres (topic-free)']
     *
     * @param string    $attribute  Type d'attribut à générer (search, filter, etc.)
     * @param array     $options    Liste des options disponibles (un tableau de la forme type => label).
     *
     * @return array
     */
    final protected function prepareSelect(string $attribute, array $options): array
    {
        foreach ($options as $type => & $label) {
            $label = sprintf('%s (%s)', $label, $this->getAttributeName($attribute, $type));
        }
        return $options;
    }

    /**
     * Génère une clé de tri pour le texte passé en paramètre.
     *
     * @param string $text
     *
     * @return string
     */
    final protected function getSortKey(string $text)
    {
        static $transliterator = null;

        if (is_null($transliterator)) {
            $transliterator = Transliterator::createFromRules("::Latin-ASCII; ::Lower; [^[:L:][:N:]]+ > ' ';");
        }

        return $transliterator->transliterate($text);
    }
}
