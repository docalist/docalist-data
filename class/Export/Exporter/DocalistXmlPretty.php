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

use Docalist\Data\Export\Exporter\DocalistXml;

/**
 * Export Docalist au format XML (formatté et indenté).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DocalistXmlPretty extends DocalistXml
{
    public function __construct()
    {
        parent::__construct();
        $this->getWriter()->setIndent(4);
    }

    public static function getID(): string
    {
        return parent::getId() . '-pretty';
    }

    public function getLabel(): string
    {
        return parent::getLabel() . __(' (formatté et indenté)', 'docalist-data');
    }

    public function getDescription(): string
    {
        return parent::getDescription() . ' ' . __(
            'Le fichier généré est formatté et indenté pour être plus facilement lisible par un humain.',
            'docalist-data'
        );
    }
}
