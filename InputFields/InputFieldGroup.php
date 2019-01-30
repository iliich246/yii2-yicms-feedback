<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsFeedback\Base\FeedbackException;
use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;

/**
 * Class InputFieldGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFieldGroup extends AbstractGroup
{
    /** @var string inputFieldTemplateReference value for current group */
    protected $fieldInputReference;

    /**
     * Sets fieldInputReference object for this
     * @param FieldInputReferenceInterface $fieldInputReference
     */
    public function setFieldInputReference(FieldInputReferenceInterface $fieldInputReference)
    {
        $this->fieldInputReference = $fieldInputReference;
    }

    public function initialize($inputFieldTemplateId = null)
    {

    }

    /**
     * @inheritdoc
     */
    public function validate()
    {

    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {

    }

    /**
     * @inheritdoc
     */
    public function save()
    {

    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function render(ActiveForm $form)
    {
        throw new FeedbackException('Not implemented for developer input fields group (not necessary)');
    }
}
