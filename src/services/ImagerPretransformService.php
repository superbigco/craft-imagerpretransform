<?php
/**
 * Imager Pretransform plugin for Craft CMS 3.x
 *
 * Pretransform any Assets on save, with Imager
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\imagerpretransform\services;

use aelvan\imager\Imager;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\web\View;
use superbig\imagerpretransform\ImagerPretransform;

use Craft;
use craft\base\Component;
use superbig\imagerpretransform\jobs\PretransformImagesJob;

/**
 * @author    Superbig
 * @package   ImagerPretransform
 * @since     2.0.0
 */
class ImagerPretransformService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param Asset $asset
     *
     * @return null|bool
     */
    public function onSaveAsset(Asset $asset)
    {
        if ($this->shouldTransform($asset)) {
            $job = new PretransformImagesJob([
                'assetIds' => [$asset->id],
            ]);

            return (bool)Craft::$app->getQueue()->push($job);
        }
    }

    /**
     * @param Asset $asset
     *
     * @return array|bool|null
     * @throws \Twig_Error_Loader
     * @throws \aelvan\imager\exceptions\ImagerException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function transformAsset(Asset $asset)
    {
        if ($asset->kind !== 'image') {
            return true;
        }

        $volumeHandle      = $asset->getVolume()->handle;
        $transforms        = $this->getTransforms($asset, $volumeHandle);
        $transformDefaults = null;
        $configOverrides   = null;

        if (!empty($transforms)) {
            // If there is any defaults/config overrides, get them
            if (isset($transforms['defaults'])) {
                $transformDefaults = $transforms['defaults'];

                unset($transforms['defaults']);
            }

            // If there is any defaults/config overrides, get them
            if (isset($transforms['configOverrides'])) {
                $configOverrides = $transforms['configOverrides'];

                unset($transforms['configOverrides']);
            }

            // Get template transforms
            $templateTransforms = array_filter($transforms, function($transform) {
                return isset($transform['template']);
            });

            $imagerTransforms = array_filter($transforms, function($transform) {
                return !isset($transform['template']);
            });

            if (!empty($templateTransforms)) {
                $this->renderTransformTemplates($asset, $templateTransforms);
            }

            if (!empty($imagerTransforms)) {
                Imager::$plugin->imager->transformImage($asset, $imagerTransforms, $transformDefaults, $configOverrides);
            }
        }
    }

    public function getTransforms(Asset $asset, $volumeHandle = null)
    {
        $transforms = ImagerPretransform::$plugin->getSettings()->transforms;

        // Check if there is a transform set for this specific Asset source handle
        if (!empty($transforms[ $volumeHandle ])) {
            $transforms = $transforms[ $volumeHandle ];
        }

        $transforms = array_map(function($settings) use ($asset) {
            return array_map(function($setting) use ($asset) {
                return is_callable($setting) ? $setting($asset) : $setting;
            }, $settings);
        }, $transforms);

        return $transforms;
    }

    /**
     * @param Asset $asset
     * @param array $transforms
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function renderTransformTemplates(Asset $asset, $transforms = [])
    {
        $view    = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();

        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        foreach ($transforms as $transform) {
            $view->renderTemplate($transform['template'], ['asset' => $asset, 'pretransform' => true]);
        }

        $view->setTemplateMode($oldMode);
    }

    public function shouldTransform(ElementInterface $element): bool
    {
        /** @var Element $element */
        $isDraft       = ImagerPretransform::$craft32 && \craft\helpers\ElementHelper::isDraftOrRevision($element);
        $isPropagating = ImagerPretransform::$craft32 && $element->propagating;

        return !$isDraft && !$isPropagating && $element instanceof Asset && $element->kind === 'image' && ($element->extension !== 'svg' && $element->mimeType !== 'image/svg+xml');
    }
}
