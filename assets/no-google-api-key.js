/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.

 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

alert(
    "Google Maps API is loaded on this page (by Docalist) but the API KEY to use is not defined. " +
    "Please add:\n" +
    "define('GOOGLE_API_KEY', 'your API key')\n" +
    "in your wp-config.php file."
);
