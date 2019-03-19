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
 * Affiche le message "accès refusé".
 *
 * Cette vue est affichée quand l'utilisateur en cours ne dispose pas des droits requis pour l'export.
 *
 * @var ExportService $this
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
$this->setTitle(__('Accès refusé', 'docalist-data')); ?>

<p class="export-intro"><?php
    _e("Vous ne disposez pas des droits nécessaires pour accéder à cette page.", 'docalist-data'); ?>
</p>

<p class ="export-back">
    <a href="javascript:history.back()"><?php
        _e('« Retour à la page précédente', 'docalist-data'); ?>
    </a>
</p>
