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
class PretransformImageJob extends BaseJob
{
    // Public Properties
    // =========================================================================

    /** @var int */
    public $assetId;

    // Public Methods
    // =========================================================================

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     *
     * @return bool
     * @throws ElementNotFoundException
     * @throws \Twig_Error_Loader
     * @throws \aelvan\imager\exceptions\ImagerException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue): bool
    {
        $assetId = $this->assetId;
        $asset   = Asset::findOne($assetId);

        if (!$asset) {
            throw new ElementNotFoundException("Couldn't find Asset #{$assetId}");
        }

        ImagerPretransform::$plugin->imagerPretransformService->transformAsset($asset);

        return true;
    }

    // Protected Methods
    // =========================================================================

    /** @inheritdoc */
    protected function defaultDescription(): string
    {
        return Craft::t('imager-pretransform', 'Pretransforming image');
    }
}
