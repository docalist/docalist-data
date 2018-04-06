<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export;

use Docalist\Data\Export\Settings\ExportSettings;
use Docalist\Data\Export\ExportService;
use Docalist\Data\Export\AdminPage\SettingsPage;
use Docalist\Data\Export\Widget\ExportWidget;
use Docalist\Data\Export\Exporter\StandardExporters;

/**
 * Initialisation du module d'export.
 *
 * Déclare le service, la page de réglages, le widget utilisés et les formats d'export disponibles.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ExportSetup
{
    /**
     * Initialise le module d'export.
     */
    public static function setup()
    {
        // Charge la configuration du module d'export
        $settings = new ExportSettings(docalist('settings-repository'));

        // Initialise le service docalist
        docalist('services')->add('docalist-data-export', new ExportService($settings));

        // Crée la page de réglages du plugin
        add_action('admin_menu', function () use ($settings) {
            new SettingsPage($settings);
        });

        // Déclare le widget "Export notices"
        add_action('widgets_init', function () {
            register_widget(ExportWidget::class);
        });

        // Déclare les exporteurs définis dans ce plugin
        add_filter('docalist_databases_get_export_formats', function (array $formats) {
            return $formats + StandardExporters::getList();
        }, 10);
    }
}
