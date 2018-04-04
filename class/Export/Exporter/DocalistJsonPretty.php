<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Exporter;

use Docalist\Data\Export\Exporter\DocalistJson;

/**
 * Export Docalist au format JSON (formatté et indenté).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistJsonPretty extends DocalistJson
{
    public function __construct()
    {
        parent::__construct();
        $this->getWriter()->setPretty(true);
    }

    public static function getID(): string
    {
        return parent::getId() . '-pretty';
    }

    public static function getLabel(): string
    {
        return parent::getLabel() . __(' (formatté et indenté)', 'docalist-data');
    }

    public static function getDescription(): string
    {
        return parent::getDescription() .
            __(' Le fichier généré est formatté pour être plus facilement lisible.', 'docalist-data');
    }
}
