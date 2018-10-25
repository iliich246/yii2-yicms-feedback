<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class FeedbackDevTranslateForm
 *
 * @property FeedbackNamesTranslatesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackDevTranslateForm extends AbstractTranslateForm
{
    /** @var string name of essence in current model language */
    public $name;
    /** @var string description of essence on current model language */
    public $description;
    /** @var Feedback db associated with this model */
    public $feedback;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => 'Feedback name on language "' . $this->language->name . '"',
            'description' => 'Description of feedback on language "' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => '50', 'tooLong' => 'Name of feedback must be less than 50 symbols'],
            ['description', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getViewName()
    {
        return '@yicms-essences/Views/translates/feedback_name_translate';
    }

    /**
     * Sets feedback of model
     * @param Feedback $feedback
     */
    public function setFeedback(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->feedback) return false;
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

        $this->currentTranslateDb = FeedbackNamesTranslatesDb::find()
            ->where([
                'common_language_id' => $this->language->id,
                'essence_id'         => $this->feedback->id,
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
        $this->currentTranslateDb = new FeedbackNamesTranslatesDb();
        $this->currentTranslateDb->common_language_id = $this->language->id;
        $this->currentTranslateDb->feedback_id        = $this->feedback->id;

        return $this->currentTranslateDb->save();
    }
}
