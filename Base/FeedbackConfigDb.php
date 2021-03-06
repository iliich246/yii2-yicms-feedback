<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsCommon\Base\AbstractModuleConfiguratorDb;

/**
 * Class FeedbackConfigDb
 *
 * @property integer $isGenerated
 * @property integer $strongGenerating
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackConfigDb extends AbstractModuleConfiguratorDb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['isGenerated', 'strongGenerating'], 'boolean'],
        ];
    }
}
