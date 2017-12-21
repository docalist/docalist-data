<?php
/**
 * This file is part of Docalist Databases.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
echo $this->view('docalist-databases-export:norequest');
?>

<small>La dernière requête exécutée ne donne aucune réponse.</small>
