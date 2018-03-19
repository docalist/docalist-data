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
 * Supprime de l'export les enregistrements complètement vides.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveEmptyRecords implements DataProcessor
{
    public function process(array $data)
    {
        return empty($data) ? null : $data;
    }
}
