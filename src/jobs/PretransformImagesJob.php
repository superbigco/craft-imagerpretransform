<?php
/**
 * Imager Pretransform plugin for Craft CMS 3.x
 *
 * Pretransform any Assets on save, with Imager
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\imagerpretransform\jobs;

use craft\elements\Asset;
use craft\errors\ElementNotFoundException;
use superbig\imagerpretransform\ImagerPretransform;

use Craft;
use craft\queue\BaseJob;

/**
 * @author    Superbig
 * @package   ImagerPretransform
 * @since     2.0.0
 */
class PretransformImagesJob extends BaseJob
{
    // Public Properties
    // =========================================================================

    /** @var array */
    public $assetIds;

    // Public Methods
    // =========================================================================

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     *
     * @return bool
     * @throws ElementNotFoundException
     * @throws \aelvan\imager\exceptions\ImagerException
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue): bool
    {
        $totalSteps  = \count($this->assetIds);
        $currentStep = 0;

        foreach ($this->assetIds as $assetId) {
            $asset = Asset::findOne($assetId);

            $currentStep++;
            $this->setProgress($queue, $currentStep / $totalSteps);

            if (!$asset) {
                throw new ElementNotFoundException("Couldn't find Asset #{$assetId}");
            }

            ImagerPretransform::$plugin->imagerPretransformService->transformAsset($asset);
        }

        return true;
    }

    // Protected Methods
    // =========================================================================

    /** @inheritdoc */
    protected function defaultDescription(): string
    {
        return Craft::t('imager-pretransform', 'Pretransforming images');
    }
}
