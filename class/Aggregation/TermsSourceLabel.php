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

namespace Docalist\Data\Aggregation;

use Docalist\Search\Aggregation\Bucket\TermsAggregation;

/**
 * Une agrégation standard de type "terms" sur le champ "filter.source.label".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TermsSourceLabel extends TermsAggregation
{
    /**
     * Constructeur
     *
     * @param array $parameters     Autres paramètres de l'agrégation.
     * @param array $options        Options d'affichage.
     */
    public function __construct(array $parameters = [], array $options = [])
    {
        !isset($parameters['size']) && $parameters['size'] = 1000;
        !isset($options['title']) && $options['title'] = __('Source', 'docalist-data');
        parent::__construct('filter.source.label', $parameters, $options);
    }
}
