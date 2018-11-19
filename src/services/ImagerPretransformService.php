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
use craft\base\ElementInterface;
use craft\elements\Asset;
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
     * @throws \aelvan\imager\exceptions\ImagerException
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

            return Imager::$plugin->imager->transformImage($asset, $transforms, $transformDefaults, $configOverrides);
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

    public function shouldTransform(ElementInterface $element): bool
    {
        return $element instanceof Asset && $element->kind === 'image' && ($element->extension !== 'svg' && $element->mimeType !== 'image/svg+xml');
    }
}
