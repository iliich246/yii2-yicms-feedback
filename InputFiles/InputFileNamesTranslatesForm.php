<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class InputFileNamesTranslatesForm
 *
 * @property InputFilesNamesTranslatesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFileNamesTranslatesForm extends AbstractTranslateForm
{
    /** @var string name of page in current model language */
    public $devName;
    /** @var string description of page on current model language */
    public $devDescription;
    /** @var string name of page in current model language */
    public $adminName;
    /** @var string description of page on current model language */
    public $adminDescription;
    /** @var InputFilesBlock associated with this model */
    private $inputFileBlock;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'devName'          => 'File name on language "' . $this->language->name . '"',
            'devDescription'   => 'Description of file on language "' . $this->language->name . '"',
            'adminName'        => 'File name on language "' . $this->language->name . '"',
            'adminDescription' => 'Description of file on language "' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['devName', 'devDescription', 'adminName', 'adminDescription'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getViewName()
    {
        return '@yicms-feedback/Views/translates/input_file_name_translate';
    }

    /**
     * Sets InputFilesBlock associated with this object
     * @param InputFilesBlock $inputFileTemplate
     */
    public function setInputFileTemplate(InputFilesBlock $inputFileTemplate)
    {
        $this->inputFileBlock = $inputFileTemplate;
    }

    /**
     * Saves record in data base
     * @return bool
     */
    public function save()
    {
        $this->getCurrentTranslateDb()->dev_name                         = $this->devName;
        $this->getCurrentTranslateDb()->dev_description                  = $this->devDescription;
        $this->getCurrentTranslateDb()->admin_name                       = $this->adminName;
        $this->getCurrentTranslateDb()->admin_description                = $this->adminDescription;
        $this->getCurrentTranslateDb()->common_language_id               = $this->language->id;
        $this->getCurrentTranslateDb()->feedback_input_files_template_id = $this->inputFileBlock->id;

        return $this->getCurrentTranslateDb()->save();
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->inputFileBlock) return false;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTranslateDb()
    {
        if ($this->currentTranslateDb) return $this->currentTranslateDb;

        $this->currentTranslateDb = InputFilesNamesTranslatesDb::find()
            ->where([
                'common_language_id'               => $this->language->id,
                'feedback_input_files_template_id' => $this->inputFileBlock->id,
            ])
            ->one();

        if (!$this->currentTranslateDb)
            $this->createTranslateDb();
        else {
            $this->devName          = $this->currentTranslateDb->dev_name;
            $this->devDescription   = $this->currentTranslateDb->dev_description;
            $this->adminName        = $this->currentTranslateDb->admin_name;
            $this->adminDescription = $this->currentTranslateDb->admin_description;
        }

        return $this->currentTranslateDb;
    }

    /**
     * @inheritdoc
     */
    protected function createTranslateDb()
    {
        $this->currentTranslateDb = new InputFilesNamesTranslatesDb();
        $this->currentTranslateDb->common_language_id        = $this->language->id;
        $this->currentTranslateDb->feedback_input_files_template_id = $this->inputFileBlock->id;

        return $this->currentTranslateDb->save();
    }
}
