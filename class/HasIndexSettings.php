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

use Docalist\Forms\Container;

/**
 * Interface d'un Indexable qui a des options d'indexation.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface HasIndexSettings
{
    /**
     * Retourne un formulaire permettant de saisir et de modifier les paramètres d'indexation.
     *
     * @return Container Un élément de formulaire.
     */
    public function getIndexSettingsForm(): Container;
}
