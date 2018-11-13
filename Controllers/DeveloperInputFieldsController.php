<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\DevFilter;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsCommon\Base\CommonException;
use Iliich246\YicmsCommon\Fields\Field;
use Iliich246\YicmsCommon\Fields\FieldsGroup;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\DevFieldsGroup;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFields\DevInputFieldsGroup;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;

/**
 * Class DeveloperInputFieldsController
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperInputFieldsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            'root' => [
//                'class' => DevFilter::className(),
//            ],
        ];
    }

    /**
     * Action for refresh dev input fields modal window
     * @param $inputFieldTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionLoadModal($inputFieldTemplateId)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#' . InputFieldsDevModalWidget::getPjaxContainerId()
        ) {
            $devInputFieldGroup = new DevInputFieldsGroup();
            $devInputFieldGroup->initialize($inputFieldTemplateId);

            return InputFieldsDevModalWidget::widget([
                'devInputFieldGroup' => $devInputFieldGroup
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for send empty input fields modal window
     * @param $inputFieldTemplateReference
     * @return string
     * @throws BadRequestHttpException
     * @throws CommonException
     * @throws \Exception
     */
    public function actionEmptyModal($inputFieldTemplateReference)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        $devInputFieldGroup = new DevInputFieldsGroup();
        $devInputFieldGroup->setInputFieldTemplateReference($inputFieldTemplateReference);
        $devInputFieldGroup->initialize();

        return InputFieldsDevModalWidget::widget([
            'devInputFieldGroup' => $devInputFieldGroup,
        ]);
    }

    /**
     * Action for update fields list container
     * @param $inputFieldTemplateReference
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionUpdateInputFieldsListContainer($inputFieldTemplateReference)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#update-input-fields-list-container'
        ) {

            $inputFieldTemplates = InputFieldTemplate::getListQuery($inputFieldTemplateReference)
                ->orderBy([InputFieldTemplate::getOrderFieldName() => SORT_ASC])
                ->all();

            return $this->render('/pjax/update-input-fields-list-container', [
                'inputFieldTemplateReference' => $inputFieldTemplateReference,
                'inputFieldTemplates'         => $inputFieldTemplates,
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for delete input field template
     * @param $inputFieldTemplateId
     * @param false|bool $deletePass
     * @return string
     * @throws BadRequestHttpException
     * @throws CommonException
     * @throws NotFoundHttpException
     */
    public function actionDeleteInputFieldTemplate($inputFieldTemplateId, $deletePass = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputFieldTemplate $inputFieldTemplate */
        $inputFieldTemplate = InputFieldTemplate::findOne($inputFieldTemplateId);

        if (!$inputFieldTemplate) throw new NotFoundHttpException('Wrong inputFieldTemplateId');

        if ($inputFieldTemplate->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new CommonException('Wrong dev password');

        $inputFieldTemplateReference = $inputFieldTemplate->input_field_template_reference;

        $inputFieldTemplate->delete();

        $inputFieldTemplates = InputFieldTemplate::getListQuery($inputFieldTemplateReference)
            ->orderBy([InputFieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-fields-list-container', [
            'inputFieldTemplateReference' => $inputFieldTemplateReference,
            'inputFieldTemplates'         => $inputFieldTemplates,
        ]);
    }

    /**
     * Action for up input field template order
     * @param $inputFieldTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputFieldTemplateUpOrder($inputFieldTemplateId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputFieldTemplate $inputFieldTemplate */
        $inputFieldTemplate = InputFieldTemplate::findOne($inputFieldTemplateId);

        if (!$inputFieldTemplate) throw new NotFoundHttpException('Wrong inputFieldTemplateId');

        $inputFieldTemplate->upOrder();

        $inputFieldTemplates = InputFieldTemplate::getListQuery($inputFieldTemplate->input_field_template_reference)
            ->orderBy([InputFieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-fields-list-container', [
            'inputFieldTemplateReference' => $inputFieldTemplate->input_field_template_reference,
            'inputFieldTemplates'         => $inputFieldTemplates,
        ]);
    }

    /**
     * Action for down input field template order
     * @param $inputFieldTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputFieldTemplateDownOrder($inputFieldTemplateId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputFieldTemplate $inputFieldTemplate */
        $inputFieldTemplate = InputFieldTemplate::findOne($inputFieldTemplateId);

        if (!$inputFieldTemplate) throw new NotFoundHttpException('Wrong inputFieldTemplateId');

        $inputFieldTemplate->downOrder();

        $inputFieldTemplates = InputFieldTemplate::getListQuery($inputFieldTemplate->input_field_template_reference)
            ->orderBy([InputFieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-fields-list-container', [
            'inputFieldTemplateReference' => $inputFieldTemplate->input_field_template_reference,
            'inputFieldTemplates'         => $inputFieldTemplates,
        ]);
    }

    /**
     * This action invert field editable value
     * @param $fieldTemplateReference
     * @param $fieldId
     * @return string
     * @throws BadRequestHttpException
     */
//    public function actionChangeFieldEditable($fieldTemplateReference, $fieldId)
//    {
//        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');
//
//        /** @var Field $field */
//        $field = Field::findOne($fieldId);
//
//        if (!$field) throw new BadRequestHttpException('Wrong fieldId = ' . $fieldId);
//
//        $field->editable = !$field->editable;
//        $field->save(false);
//
//        $fieldsGroup = new FieldsGroup();
//        $fieldsGroup->initializePjax($fieldTemplateReference, $field);
//
//        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/pjax/fields', [
//            'fieldsGroup'            => $fieldsGroup,
//            'fieldTemplateReference' => $fieldTemplateReference,
//            'success'                => true,
//        ]);
//    }

    /**
     * This action invert field editable value via ajax without render of template
     * @param $fieldTemplateReference
     * @param $fieldId
     * @return bool
     * @throws BadRequestHttpException
     * @throws CommonException
     */
//    public function actionChangeFieldEditableAjax($fieldTemplateReference, $fieldId)
//    {
//        if (!Yii::$app->request->isAjax) throw new BadRequestHttpException('Not Pjax');
//
//        /** @var Field $field */
//        $field = Field::findOne($fieldId);
//
//        if (!$field) throw new BadRequestHttpException('Wrong fieldId = ' . $fieldId);
//
//        $field->editable = !$field->editable;
//
//        if ($field->save(false)) return true;
//
//        throw new CommonException('Can`t update field');
//    }
}
