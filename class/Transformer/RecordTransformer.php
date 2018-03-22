<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Transformer;

use Docalist\Data\Transformer\Transformer;
use Docalist\Data\Record;

/**
 * Un Transformer qui travaille sur des enregistrements Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface RecordTransformer extends Transformer
{
    /**
     * Transforme ou filtre les données passées en paramètre.
     *
     * @param array $data Les données à traiter.
     *
     * @return Record|null Les données modifiées ou null pour filtrer les données.
     */
    public function transform(Record $record);
}
