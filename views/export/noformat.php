<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Views;

use Docalist\Data\Export\ExportService;

/**
 * Affiche un message "aucun format disponible"
 *
 * @var ExportService   $this
 * @var int             $count Nombre total d'enregistrements dans la sélection.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
$this->setTitle(__('Export impossible', 'docalist-data')); ?>

<p class="export-intro"><?php
    _e(
        "Les contenus sélectionnés ne peuvent pas être exportés avec les formats actuellement disponibles.",
        'docalist-data'
    ); ?>
</p>

<p class ="export-back">
    <a href="javascript:history.back()"><?php
        _e('« Retour à la page précédente', 'docalist-data'); ?>
    </a>
</p>
