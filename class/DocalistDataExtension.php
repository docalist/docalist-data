<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Data;

use Docalist\AdminNotices;
use Docalist\Container\ContainerBuilderInterface;
use Docalist\Container\ContainerInterface;
use Docalist\Data\Export\AdminPage\SettingsPage;
use Docalist\Data\Export\ExportService;
use Docalist\Data\Export\Settings\ExportSettings;
use Docalist\Data\Export\Widget\ExportWidget;
use Docalist\Data\Pages\AdminDatabases;
use Docalist\Data\Settings\Settings;
use Docalist\Kernel\KernelExtension;
use Docalist\Repository\SettingsRepository;
use Docalist\Search\QueryDSL;
use Docalist\Search\SearchEngine;
use Docalist\Sequences;
use Docalist\Views;

final class DocalistDataExtension extends KernelExtension
{
    public function build(ContainerBuilderInterface $containerBuilder): void
    {
        $containerBuilder

        // Ajoute nos vues au service "views"
        ->listen(Views::class, static function (Views $views): void {
            $views->addDirectory('docalist-data', DOCALIST_DATA_DIR.'/views');
        })

        // Configuration du plugin
        ->set(Settings::class, [SettingsRepository::class])

        // Page AdminDatabases
        ->set(AdminDatabases::class, [Settings::class, Sequences::class])

        // Configuration de l'export
        ->set(ExportSettings::class, [SettingsRepository::class])

        ->set(ExportService::class, [ExportSettings::class, Views::class, QueryDSL::class])
        ->deprecate('docalist-data-export', ExportService::class, '2023-11-27')

        ->set(SettingsPage::class, [ExportSettings::class, AdminNotices::class])

        ->set(ExportWidget::class, [SearchEngine::class, ExportService::class])

        // Plugin wordpress
        ->set(DocalistDataPlugin::class, static fn (ContainerInterface $container): DocalistDataPlugin => new DocalistDataPlugin(
            $container
        ))
        // ->alias('docalist-data', DocalistDataPlugin::class) // pas encore deprecated, utilisé partout dans le thème svb
        ->deprecate('docalist-data', DocalistDataPlugin::class, '2023-11-27')

        ;
    }
}
