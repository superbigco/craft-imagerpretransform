<?php
/**
 * Imager Pretransform plugin for Craft CMS 3.x
 *
 * Pretransform any Assets on save, with Imager
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\imagerpretransform;

use craft\elements\Asset;
use craft\events\ElementEvent;
use craft\events\RegisterElementActionsEvent;
use craft\services\Elements;
use superbig\imagerpretransform\elementactions\PretransformAction;
use superbig\imagerpretransform\services\ImagerPretransformService as ImagerPretransformServiceService;
use superbig\imagerpretransform\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class ImagerPretransform
 *
 * @author    Superbig
 * @package   ImagerPretransform
 * @since     2.0.0
 *
 * @property  ImagerPretransformServiceService $imagerPretransformService
 */
class ImagerPretransform extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ImagerPretransform
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '2.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'superbig\imagerpretransform\console\controllers';
        }

        $this->setComponents([
            'imagerPretransformService' => "superbig\\imagerpretransform\\services\\ImagerPretransformService",
        ]);

        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, function(ElementEvent $event) {
            $element = $event->element;

            if (self::$plugin->imagerPretransformService->shouldTransform($element)) {
                self::$plugin->imagerPretransformService->onSaveAsset($element);
            }
        });

        Event::on(Asset::class, Asset::EVENT_REGISTER_ACTIONS, function(RegisterElementActionsEvent $event) {
            $event->actions[] = Craft::$app->getElements()->createAction([
                'type' => PretransformAction::class,
            ]);
        });

        Craft::info(
            Craft::t(
                'imager-pretransform',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
