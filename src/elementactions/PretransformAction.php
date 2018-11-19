<?php
/**
 * Imager Pretransform plugin for Craft CMS 3.x
 *
 * Pretransform any Assets on save, with Imager
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\imagerpretransform\elementactions;

use craft\elements\db\ElementQueryInterface;
use superbig\imagerpretransform\ImagerPretransform;
use craft\base\ElementAction;
use superbig\imagerpretransform\jobs\PretransformImagesJob;

/**
 * @author    Superbig
 * @package   ImagerPretransform
 * @since     2.0.0
 */
class PretransformAction extends ElementAction
{
    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return \Craft::t('imager-pretransform', 'Pretransform');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $job = new PretransformImagesJob([
            'assetIds' => $query->ids(),
        ]);

        return \Craft::$app->getQueue()->push($job);
    }
}
