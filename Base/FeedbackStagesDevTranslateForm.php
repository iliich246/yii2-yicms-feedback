<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class FeedbackStagesDevTranslateForm
 *
 * @property FeedbackStagesNamesTranslatesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackStagesDevTranslateForm extends AbstractTranslateForm
{
    /** @var string name of essence in current model language */
    public $name;
    /** @var string description of essence on current model language */
    public $description;
    /** @var FeedbackStages db associated with this model */
    public $feedbackStage;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => 'Feedback stage name on language "' . $this->language->name . '"',
            'description' => 'Description of feedback stage on language "' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => '50', 'tooLong' => 'Name of feedback stage must be less than 50 symbols'],
            ['description', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getViewName()
    {
        return '@yicms-feedback/Views/translates/feedback-stage-name-translate';
    }

    /**
     * Sets feedback stage of model
     * @param FeedbackStages $feedbackStage
     */
    public function setFeedbackStage(FeedbackStages $feedbackStage)
    {
        $this->feedbackStage = $feedbackStage;
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->feedbackStage) return false;
        return true;
    }

    /**
     * Saves new data in data base
     * @return bool
     */
    public function save()
    {
        $this->currentTranslateDb->name        = $this->name;
        $this->currentTranslateDb->description = $this->description;

        return $this->currentTranslateDb->save();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTranslateDb()
    {
        if ($this->currentTranslateDb) return $this->currentTranslateDb;

        $this->currentTranslateDb = FeedbackStagesNamesTranslatesDb::find()
            ->where([
                'common_language_id' => $this->language->id,
                'stage_id'           => $this->feedbackStage->id,
            ])
            ->one();

        if (!$this->currentTranslateDb)
            $this->createTranslateDb();
        else {
            $this->name        = $this->currentTranslateDb->name;
            $this->description = $this->currentTranslateDb->description;
        }

        return $this->currentTranslateDb;
    }

    /**
     * @inheritdoc
     */
    protected function createTranslateDb()
    {
        $this->currentTranslateDb = new FeedbackStagesNamesTranslatesDb();
        $this->currentTranslateDb->common_language_id = $this->language->id;
        $this->currentTranslateDb->stage_id           = $this->feedbackStage->id;

        return $this->currentTranslateDb->save();
    }
}
