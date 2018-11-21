<?php
/**
 * Imager Pretransform plugin for Craft CMS 3.x
 *
 * Pretransform any Assets on save, with Imager
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\imagerpretransform\console\controllers;

use craft\elements\Asset;
use superbig\imagerpretransform\ImagerPretransform;

use Craft;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Default Command
 *
 * @author    Superbig
 * @package   ImagerPretransform
 * @since     2.0.0
 */
class DefaultController extends Controller
{
    public $volume;
    public $folderId;
    public $includeSubfolders;

    // Public Methods
    // =========================================================================

    public function options($actionsID)
    {
        $options = parent::options($actionsID);

        return array_merge($options, [
            'volume',
            'folderId',
            'includeSubfolders',
        ]);
    }

    public function optionAliases()
    {
        return [
            'v' => 'volume',
            's' => 'includeSubfolders',
        ];
    }

    /**
     * Pretransform images by Volume/Folder
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $query  = null;
        $assets = [];

        if (empty($this->volume) && empty($this->folderId)) {
            $this->error("No source handle or folderId was specified");

            return ExitCode::NOINPUT;
        }

        if (!empty($this->volume)) {
            $volumes = Craft::$app->getVolumes()->getAllVolumes();
            $volume  = null;

            foreach ($volumes as $volumeCheck) {
                if ($volumeCheck->handle === $this->volume) {
                    $volume = $volumeCheck;

                    break;
                }
            }

            if ($volume) {
                $query = Asset::find()
                              ->volume($volume)
                              ->kind('image')
                              ->limit(null);

                if ($this->includeSubfolders && !$this->folderId) {
                    $folderId = Craft::$app->getVolumes()->ensureTopFolder($volume);

                    $query->folderId($folderId);
                }
            }
        }

        if (!empty($this->folderId)) {
            $query = Asset::find()
                          ->folderId($this->folderId)
                          ->kind('image')
                          ->limit(null);
        }

        if ($this->includeSubfolders) {
            $this->success("> Including subfolders.");
            $query->includeSubfolders(true);
        }

        $assets = $query->all();

        if (empty($assets)) {
            $this->error("No assets found");

            return ExitCode::OK;
        }

        $total   = count($assets);
        $current = 0;

        $this->success("> Processing {$total} images.");

        Console::startProgress(0, $total);

        foreach ($assets as $asset) {
            $current++;

            Console::updateProgress($current, $total);

            ImagerPretransform::$plugin->imagerPretransformService->transformAsset($asset);
        }

        Console::endProgress();

        $this->success("> Done.");

        return ExitCode::OK;
    }

    public function success($text = '')
    {
        $this->stdout("$text\n", Console::FG_GREEN);
    }

    public function error($text = '')
    {
        $this->stdout("$text\n", Console::FG_RED);
    }
}
