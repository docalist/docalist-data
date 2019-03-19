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

use Docalist\Type\TypedText;
use Docalist\Type\Text;
use Docalist\Type\Url;

/**
 * Champ standard "source" : informations sur la provenance des données de l'enregistrement.
 *
 * @property TableEntry $type   Code de provenance.
 * @property Url        $value  Url de provenance.
 * @property Text       $value  Note, info, remarque.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SourceField extends TypedText
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'source',
            'repeatable' => true,
            'label' => __('Source', 'docalist-data'),
            'description' => __('Informations sur la provenance des informations.', 'docalist-data'),
            'fields' => [
                'type' => [
                    'label' => __('Source', 'docalist-data'),
                    'description' => __('Code de provenance.', 'docalist-data'),
                    'table' => 'table:source-type',
                ],
                'url' => [
                    'type' => Url::class,
                    'label' => __('Url', 'docalist-data'),
                    'description' => __('Url de provenance.', 'docalist-data'),
                ],
                'value' => [
                    'label' => __('Précisions', 'docalist-data'),
                    'description' => __('Note, remarque...', 'docalist-data'),
                ],
            ]
        ];
    }

    public function filterEmpty(bool $strict = true): bool
    {
        // TypedText considère qu'on est vide si on n'a que le type
        // Dans notre cas, il fuat juste que l'un des champs soit rempli
        return $this->filterEmptyProperty('type', $strict)
            && $this->filterEmptyProperty('url', $strict)
            && $this->filterEmptyProperty('value', $strict);
    }
}
