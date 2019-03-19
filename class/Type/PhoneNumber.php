<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Type;

use Docalist\Type\Text;

/**
 * Un numéro de téléphone.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PhoneNumber extends Text
{
    /*
     * Evolutions futures :
     *
     * - Pour le moment, un PhoneNumber est juste un champ texte.
     * - Normaliser le stockage des numéros de téléphones (format E164)
     * - Gérer le formattage (ajout ou pas du +33 selon que c'est un numéro à l'étranger ou pas)
     * - Générer un input tél pour la saisie.
     * cf. aussi rfc3966 : https://tools.ietf.org/html/rfc3966
     */

    public static function loadSchema()
    {
        return [
            'label' => __('Téléphone', 'docalist-data'),
            'description' => __('Numéro de téléphone.', 'docalist-data'),
        ];
    }
}
