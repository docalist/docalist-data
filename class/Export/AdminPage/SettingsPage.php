<?php declare(strict_types=1);
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Data\Export\AdminPage;

use Docalist\AdminPage;
use Docalist\Data\Export\Settings\ExportSettings;
use Exception;

/**
 * Options de configuration du plugin.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class SettingsPage extends AdminPage
{
    /**
     * Paramètres du plugin.
     *
     * @var ExportSettings
     */
    protected $settings;

    /**
     * Crée la page de réglages des paramètres du plugin.
     *
     * @param ExportSettings $settings Paramètres du plugin.
     */
    public function __construct(ExportSettings $settings)
    {
        $this->settings = $settings;

        parent::__construct(
            'docalist-data-export-settings',           // ID
            'options-general.php',                     // page parent
            __('Export Docalist', 'docalist-data')   // libellé menu
        );
    }

    protected function getDefaultAction()
    {
        return 'ExportSettings';
    }

    /**
     * Paramètres de l'export.
     */
    public function actionExportSettings()
    {
        if ($this->isPost()) {
            try {
                $_POST = wp_unslash($_POST);
                $this->settings->exportpage = (int) $_POST['exportpage'];
                $this->settings->limit = $_POST['limit'];
                // $settings->validate();
                $this->settings->filterEmpty(false);
                $this->settings->save();

                docalist('admin-notices')->success(__("Les options d'export ont été enregistrées.", 'docalist-data'));

                return $this->redirect($this->getUrl($this->getDefaultAction()), 303);
            } catch (Exception $e) {
                docalist('admin-notices')->error($e->getMessage());
            }
        }

        return $this->view('docalist-data:export/settings/export', [
            'settings' => $this->settings,
        ]);
    }
}
