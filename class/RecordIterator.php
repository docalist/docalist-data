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

namespace Docalist\Data;

use Docalist\Search\Iterator\ScrollIterator;
use Docalist\Data\DocalistDataPlugin;
use Generator;
use InvalidArgumentException;
use stdClass;

/**
 * Un itérateur de références (pour l'export).
 *
 * @extends ScrollIterator<Record>
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RecordIterator extends ScrollIterator
{
    /**
     * Retourne un générateur qui permet d'itérer sur les notices docalist retournés par la requête.
     *
     * @return Generator Les clés retournées correspondent au Post_ID de la notice et la valeur associée est
     * l'objet Record contenant la notice.
     *
     * @return Generator
     */
    public function getIterator(): Generator
    {
        // Récupère le service docalist-data utilisé pour charger les notices
        $docalistDataPlugin = docalist(DocalistDataPlugin::class);

        // Parcourt tous les hits et essaie de charger la notice correspondante
        /** @var stdClass $hit */
        foreach (parent::getIterator() as $hit) {
            $id = (int) $hit->_id;
            try {
                yield $id => $docalistDataPlugin->getRecord($id);
            } catch (InvalidArgumentException $e) {
                // on ignore
            }
        }

        /*
         * getRecord() génère une exception dans deux cas :
         *
         * 1. Le post n'existe pas : ça peut se produire car on utilise une requête scroll (index "figé"),
         *    et il se peut que la notice correspondant au hit retourné ait été supprimée pendant le scroll.
         *    Dans ce cas on ignore l'erreur.
         *
         * 2. Le post indiqué n'est pas un record docalist. Cela ne devrait pas se produire car avant
         *    d'utiliser un RecordIterator, on doit s'assurer qu'on n'a que des Record dans la requête
         *    (en ajoutant des filtres sur les types).
         *    On ne devrait pas ignorer l'erreur dans ce cas, mais comme on ne peut pas distinguer les
         *    deux cas, on ignore tout de même.
         */
    }
}
