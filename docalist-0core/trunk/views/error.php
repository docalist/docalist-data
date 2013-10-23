<?php
/**
 * This file is part of the 'Docalist Core' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Core
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist;

/**
 * Affiche un message d'erreur
 */

/* @var $h2 string */
/* @var $h3 string */
/* @var $message string */
/* @var $back string */

! isset($h2) && $h2 = __('Erreur', 'docalist-core');
$href = isset($back) ? esc_url($back) : 'javascript:history.go(-1)'
?>

<div class="wrap">

<?= screen_icon() ?>
<h2><?=$h2 ?></h2>

<div class="error">
    <?php if (isset($h3)) :?>
        <h3><?= $h3 ?></h3>
    <?php endif ?>

    <?php if (isset($message)) :?>
        <p><?= $message ?></p>
    <?php endif ?>

    <p>
        <a href="<?=$href?>" class="button-primary">
            <?= __('Ok', 'docalist-core') ?>
        </a>
    </p>
</div>

</div>