<?php
/**
 * This file is part of the 'Docalist Databases' plugin.
 *
 * Copyright (C) 2015-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Databases\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Databases\Export\Views;

use Docalist\Databases\Export\Plugin;

/**
 * Affiche le message "aucune réponse".
 *
 * Cette vue est affichée quand la dernière requête exécutée ne donne aucune
 * réponses.
 * Par défaut, on se contente d'afficher la vue "norequest".
 *
 * @var Plugin $this
 */
echo $this->view('docalist-databases-export:norequest');
?>

<small>La dernière requête exécutée ne donne aucune réponse.</small>
