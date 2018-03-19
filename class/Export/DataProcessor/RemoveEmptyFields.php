<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\DataProcessor;

use Docalist\Data\Export\DataProcessor;

/**
 * Supprime récursivement les éléments vides dans les données à exporter.
 *
 * Un élément est considéré comme vide s'il contient une chaine vide, la valeur null ou un tableau vide.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveEmptyFields implements DataProcessor
{
    public function process(array $data)
    {
        return array_filter($data, function ($value) {
            is_array($value) && $value = $this->process($value);

            return ! ($value === '' | $value === null | $value === []);
        });
    }
}
