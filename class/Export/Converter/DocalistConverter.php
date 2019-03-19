<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Converter;

use Docalist\Data\Export\Converter;
use Docalist\Data\Record;

/**
 * Convertisseur Docalist pour l'export de données Docalist.
 *
 * Retourne les données brutes présentes dans l'enregistrement Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistConverter implements Converter
{
    public function supports(string $className): bool
    {
        return is_a($className, Record::class, true);
    }

    public function __invoke(Record $record)
    {
        return $record->getPhpValue();
    }
}
