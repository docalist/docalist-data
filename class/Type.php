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

use Docalist\Data\Record;

use function Docalist\deprecated;

deprecated(__CLASS__, 'Record', '2019-03-07');

/**
 * Compatibilité ascendante : ancien nom de la classe Record.
 *
 * @deprecated
 */
class Type extends Record
{
}
