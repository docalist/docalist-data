<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\Views;

use Docalist\Data\Export\Plugin;

/**
 * Affiche le formulaire d'export.
 *
 * @var Plugin   $this
 * @var array    $types     Types des notices (libellé => count)
 * @var int      $total     Nombre total de hits obtenus (notices à exporter).
 * @var int      $max       Nombre maximum de notices exportables.
 * @var Format[] $formats   Liste des formats d'export disponibles.
 * @var string   $format    Format d'export actuellement sélectionner.
 * @var boolean  $mail      Envoyer par mail.
 * @var boolean  $zip       Compresser le fichier.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

// Crée le détail par type des notices qui seront exportées
if (count($types) === 1) {
    $detail = lcfirst(key($types));
} else {
    $detail = [];
    foreach ($types as $label => $count) {
        $detail[] = sprintf(__('%s : %d', 'docalist-data'), lcfirst($label), $count);
    }
    $detail = implode(', ', $detail);
}
?>
<style>
    .export table {
        width: 98%;
    }
    .export table td {
        vertical-align: top;
    }
    .export table th {
        width: 2em;
        vertical-align: top;
    }
    .export label {
        display: inline; /* because of bootstrap : display block */
    }
    .export-description {
        font-style: italic;
    }
    .export-details {
        display: none;
    }
    .export-more {
        color: #aaa;
        background-color: #eee;
        display: inline-block;
        text-align: center;
        width: 16px;
        height: 16px;
        border-radius: 8px;
    }
    .export-more:hover, .export-more.export-active {
        text-decoration: none !important;
        background-color: #f4f4f4;
        color: #444;
        font-weight: bold;
    }

</style>

<form class="export">
    <p>
        <?php
            $limit = '';
            if ($total > $max) {
                $limit = sprintf(__(', seules les %d premières notices seront exportées', 'docalist-data'), $max);
            }
            printf(
                __('Votre sélection contient <abbr title="%s">%d notice(s)</abbr>%s.', 'docalist-data'),
                $detail, $total, $limit
            );
        ?>
    </p>

    <h3><?=__('Format', 'docalist-data')?></h3>
    <table>
        <?php foreach($formats as $name => $fmt): /** @var Format $fmt */ ?>
        <tr>
            <th>
                <input type="radio" name="format" id="format-<?=$name?>" value="<?=$name?>" <?php checked($format, $name)?> />
                &nbsp;
            </th>
            <td>
                <p class="export-label">
                    <label for="format-<?=$name?>"><?=$fmt->getlabel()?></label>
                </p>
                <p class="export-description">
                    <?=$fmt->getDescription()?> <a class="export-more" href="#">+</a>
                </p>
                <ul class="export-details">
                    <li><?=$fmt->converter()->getLabel() . ' : ' . lcfirst($fmt->converter()->getDescription())?></li>
                    <li><?=$fmt->exporter()->getLabel() . ' : ' . lcfirst($fmt->exporter()->getDescription())?></li>
                </ul>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<!--
    <h3><?=__('Options', 'docalist-data')?></h3>
    <table>
        <tr>
            <th>
                <input type="checkbox" name="mail" id="mail" value="1" <?php checked($mail, true)?> />&nbsp;
            </th>
            <td>
                <p>
                    <label for="mail">
                        <?=__('Envoyez-moi le fichier par messagerie', 'docalist-data')?>
                    </label>
                </p>
            </td>
        </tr>
        <tr>
            <th>
                <input type="checkbox" name="zip" id="zip" value="1" <?php checked($zip, true)?> />&nbsp;
            </th>
            <td>
                <p>
                    <label for="zip">
                        <?=__('Compresser le fichier (zip)', 'docalist-data')?>
                    </label>
                </p>
            </td>
        </tr>
    </table>
 -->
    <h3>
        <button class="btn" type="submit"><?=__("Lancer l'export...", 'docalist-data')?></button>
    </h3>
    <input type="hidden" name="go" value="1" />
</form>

<script type="text/javascript">
(function($) {
    $(document).ready(function () {
        $(document).on('click', '.export-more', function(e) {
            var details = $(this).parent().next('ul');

            if ($(this).is('.export-active')) {
                $('.export-more').removeClass('export-active');
                $('.export-details').slideUp();
            } else {
                $('.export-more').not(this).removeClass('export-active');
                $('.export-details').not(details).slideUp();

                $(this).addClass('export-active');
                $(details).slideDown();
            }

            e.preventDefault();

            return false;
        });
    });
}(jQuery));
</script>
