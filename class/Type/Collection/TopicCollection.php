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

namespace Docalist\Data\Type\Collection;

use Docalist\Type\Collection;
use Docalist\Forms\TopicsInput;
use Docalist\Data\Type\Topic;
use Docalist\Forms\Element;
use Docalist\Type\Collection\TypedValueCollection;
use InvalidArgumentException;

/**
 * Une collection de Topic.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TopicCollection extends TypedValueCollection
{
    public function getEditorForm($options = null): Element
    {
        $form = new TopicsInput($this->schema->name(), $this->schema->getField('type')->table());

        $form
            ->addClass($this->getEditorClass())
            ->setLabel($this->getOption('label', $options))
            ->setDescription($this->getOption('description', $options));

        return $form;
    }

    /**
     * Recherche le code de tous les types de topics qui sont associés à une table de type 'thesaurus'.
     *
     * La méthode regarde la table des topics indiquées dans le schéma du sous-champ `type` et retourne tous les
     * codes de topic qui sont associés à une table de lookup de type thesaurus.
     *
     * @return string[] Un tableau de la forme table => topic (les clés indiquent la table utilisée).
     */
    public function getThesaurusTopics()
    {
        // Ouvre la table des topics indiquée dans le schéma du champ 'type'
        list(, $name) = explode(':', $this->schema->getField('type')->table());
        $table = docalist('table-manager')->get($name);

        // Recherche toutes les entrées qui sont associées à une table de type 'thesaurus'
        $topics = [];
        foreach ($table->search('code,source', 'source LIKE "thesaurus:%"') as $code => $source) {
            $topics[substr($source, 10)] = $code; // supprime le préfixe 'thesaurus:'
        }

        // Ok
        return $topics;
    }

    /**
     * {@inheritDoc}
     *
     * Cette méthode surcharge la méthode merge() héritée de Collection pour combiner ensemble les termes des topics
     * qui ont le même type : si deux topics ont le même type, leurs listes de termes sont fusionnées et
     * dédoublonnées. Les topics qui ont des types différents sont simplement ajoutés à la collection retournée.
     *
     * Remarque : la méthode teste uniquement le type des topics, elle ne vérifie pas que les topics utilisent
     * les mêmes tables d'autorité.
     *
     * Exemple :
     *
     * <code>
         * col1 :               [ {type:a, value:[a1,a2]}, {type:b, value:[b1,b2]} ]
         * col2 :               [ {type:a, value:[a2,a3]}, {type:c, value:[c1]} ]
         * col1->merge(col2) :  [ {type:a, value:[a1,a2,a3]}, {type:b, value:[b1,b2]}, {type:c, value:[c1]} ]
     * </code>
     *
     * @param Collection $collection
     *
     * @return Collection Retourne une nouvelle collection (les collections d'origine ne sont pas modifiées).
     */
    public function merge(Collection $collection): Collection
    {
        // Vérifie que les collections sont compatibles (i.e. des TopicCollection ou des classes enfants)
        if (!is_a($collection, get_class($this))) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not mergeable with "%s"',
                get_class($collection),
                get_class($this)
            ));
        }

        // On part du tableau de Topic qu'on a déjà
        $topics = $this->phpValue; /** @var Topic[] $topics */

        // Fusionne la TopicCollection passée en paramètre
        foreach ($collection->phpValue as $type => $topic) { /** @var Topic $topic */
            // Type de topic commun aux deux collections, on fusionne les termes
            if (isset($topics[$type])) {
                // On ne veut pas modifier la collection d'origine, donc on clone le Topic
                $topics[$type] = clone $topics[$type];

                // Fusionne les termes
                $topics[$type]->phpValue['value'] = $topics[$type]->value->merge($topic->value);
                continue;
            }

            // Topic qu'on avait pas, on l'ajoute à notre liste (pas de copie, on a une référence sur le même Topic)
            $topics[$type] = $topic;
        }

        // Crée une nouvelle collection contenant les topics obtenus
        $result = new static([], $this->getSchema());
        $result->phpValue = $topics;

        // Ok
        return $result;
    }

    /**
     * Retourne le libellé des termes présents dans les topics de la collection.
     *
     * La méthode fusionne tous les topics ensemble et retourne un tableau qui indique, pour chaque code de topic,
     * le libellé correspondant tel qu'il figure dans la table d'autorité associée au topic.
     *
     * Si un terme n'existe pas dans la table d'autorité, c'est son code qui est retourné comme label.
     *
     * Remarque : si deux termes issus de tables d'autorité différentes ont le même code, une seule entrée est
     * retournée avec le libellé du premier topic trouvé.
     *
     * @param array $include    Liste des types de topics à inclure : si le tableau n'est pas vide, seuls
     *                          les termes des topics indiqués sont retournés.
     *
     * @param array $exclude    Liste des types de topics à exclure : si le tableau n'est pas vide, les
     *                          termes des topics indiqués ne sont pas retournés.
     *
     * @param int   $limit      Nombre maximum de termes à retourner (0 = pas de limite).
     *
     * @return string[] Un tableau de la forme code => libellé.
     */
    public function getTermsLabel(array $include = [], array $exclude = [], int $limit = 0): array
    {
        // Si on a une limite, vérifie que c'est un nombre positif (effet de bord avec array_slice sinon)
        if ($limit && $limit < 0) {
            throw new InvalidArgumentException('Limit must be positive');
        }

        // Parcourt tous les topics
        $terms = [];
        foreach ($this->phpValue as $type => $topic) { /** @var Topic $topic */
            // Exclut les types dont on ne veut pas
            if ($include && !in_array($type, $include, true) || $exclude && in_array($type, $exclude, true)) {
                continue;
            }

            // Fusionne les termes
            $terms += $topic->getTermsLabel();

            // Sort au plus vite si on a atteint la limite demandée
            if ($limit && count($terms) >= $limit) {
                break;
            }
        }

        // Tronque la liste si on a trop de termes
        if ($limit && count($terms) > $limit) {
            $terms = array_slice($terms, 0, $limit);
        }

        // Ok
        return $terms;
    }
}
