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

use Docalist\Schema\Schema;
use Docalist\Type\Collection;

/**
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Grid extends Schema
{
    protected function addSubFields(Schema $field, Schema $base)
    {
        if ($base->hasFields()) {
            $fields = [];
            foreach ($base->getFields() as $name => $subfield) {
                $fields[$name] = new Schema(['name' => $name]);
                $this->addSubFields($fields[$name], $subfield);
            }
            $field->properties['fields'] = $fields;
        }
    }

    public function initSubfields(Schema $base)
    {
        foreach ($this->getFields() as $name => $field) {
            if ($base->hasField($name)) {
                $this->addSubFields($field, $base->getField($name));
            }
        }
    }

    public function mergeWith(array $data)
    {
        return new self($this->mergeProperties($this->value(), $data));
    }

    public static function getCollectionClass(): string
    {
        return Collection::class;
    }
}
