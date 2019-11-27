<?php
/**
 * Imager Pretransform plugin for Craft CMS 3.x
 *
 * Pretransform any Assets on save, with Imager
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\imagerpretransform\models;

use superbig\imagerpretransform\ImagerPretransform;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   ImagerPretransform
 * @since     2.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /** @var bool */
    public $enabled = true;

    /** @var bool */
    public $processImagesInJobs = false;

    /** @var array */
    public $transforms = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['transforms', 'default', 'value' => []],
        ];
    }
}
