<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Iliich246\YicmsFeedback\InputFields\DevInputFieldsGroup;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
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
     * Action for send empty fields modal window for existed modal window like file modal or images modal
     * @param $inputFieldTemplateReference
     * @param $pjaxName
     * @param $modalName
     * @return string
     * @throws BadRequestHttpException
     * @throws CommonException
     */
    /*
    public function actionEmptyModalDependent($inputFieldTemplateReference,
                                              $pjaxName,
                                              $modalName)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        $devInputFieldGroup = new DevInputFieldsGroup();
        $devInputFieldGroup->setInputFieldTemplateReference($inputFieldTemplateReference);
        $devInputFieldGroup->initialize();

        if (Yii::$app->request->post('_saveAndBack'))
            $returnBack = true;
        else
            $returnBack = false;

        //try to load validate and save field via pjax
        if ($devInputFieldGroup->load(Yii::$app->request->post()) && $devInputFieldGroup->validate()) {

            if (!$devInputFieldGroup->save()) {
                //TODO: bootbox error
            }

            $devInputFieldGroup->scenario = DevInputFieldsGroup::SCENARIO_UPDATE;
        }

        return $this->renderAjax('/../Fields/views/fields_dev_for_modals_dependents.php', [
            'devFieldGroup' => $devFieldGroup,
            'returnBack'    => $returnBack,
            'pjaxName'      => $pjaxName,
            'modalName'     => $modalName
        ]);
    }
    */

    /**
     * Load modal window with existed field with dependent
     * @param $fieldTemplateReference
     * @param $fieldTemplateId
     * @param $pjaxName
     * @param $modalName
     * @return string
     * @throws BadRequestHttpException
     * @throws CommonException
     */
    /*
    public function actionLoadModalDependent($fieldTemplateReference,
                                             $fieldTemplateId,
                                             $pjaxName,
                                             $modalName)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        $devFieldGroup = new DevFieldsGroup();
        $devFieldGroup->setFieldTemplateReference($fieldTemplateReference);
        $devFieldGroup->initialize($fieldTemplateId);

        if (Yii::$app->request->post('_saveAndBack'))
            $returnBack = true;
        else
            $returnBack = false;

        //try to load validate and save field via pjax
        if ($devFieldGroup->load(Yii::$app->request->post()) && $devFieldGroup->validate()) {

            if (!$devFieldGroup->save()) {
                //TODO: bootbox error
            }
        }

        return $this->renderAjax('/../Fields/views/fields_dev_for_modals_dependents.php', [
            'devFieldGroup' => $devFieldGroup,
            'returnBack'    => $returnBack,
            'pjaxName'      => $pjaxName,
            'modalName'     => $modalName,
        ]);
    }
    */

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
     * Action for update fields list container in modal windows of other entities like files or images
     * @param $fieldTemplateReference
     * @param $pjaxName
     * @param $modalName
     * @return string
     * @throws BadRequestHttpException
     */
    /*
    public function actionUpdateFieldsListContainerDependent($fieldTemplateReference, $pjaxName, $modalName)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('No pjax');

        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($fieldTemplateReference)
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $fieldTemplatesSingle = FieldTemplate::getListQuery($fieldTemplateReference)
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->renderAjax('/pjax/update-input-fields-list-container-dependent', [
            'fieldTemplateReference'     => $fieldTemplateReference,
            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
            'pjaxName'                   => $pjaxName,
            'modalName'                  => $modalName,
        ]);
    }
    */

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
     * Action for delete field template in dependent modal window
     * @param $fieldTemplateId
     * @param $pjaxName
     * @param $modalName
     * @param bool|false $deletePass
     * @return string
     * @throws BadRequestHttpException
     * @throws CommonException
     * @throws NotFoundHttpException
     */
//    public function actionDeleteFieldTemplateDependent($fieldTemplateId,
//                                                       $pjaxName,
//                                                       $modalName,
//                                                       $deletePass = false)
//    {
//        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');
//
//        /** @var FieldTemplate $fieldTemplate */
//        $fieldTemplate = FieldTemplate::findOne($fieldTemplateId);
//
//        if (!$fieldTemplate) throw new NotFoundHttpException('Wrong fieldTemplateId');
//
//        if ($fieldTemplate->isConstraints())
//            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
//                throw new CommonException('Wrong dev password');
//
//        $fieldTemplateReference = $fieldTemplate->field_template_reference;
//
//        $fieldTemplate->delete();
//
//        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($fieldTemplateReference)
//            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
//            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
//            ->all();
//
//        $fieldTemplatesSingle = FieldTemplate::getListQuery($fieldTemplateReference)
//            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
//            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
//            ->all();
//
//        return $this->renderAjax('/pjax/update-input-fields-list-container-dependent', [
//            'fieldTemplateReference'     => $fieldTemplateReference,
//            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
//            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
//            'pjaxName'                   => $pjaxName,
//            'modalName'                  => $modalName,
//        ]);
//    }

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
     * Action for up field template order in dependent modal window
     * @param $fieldTemplateId
     * @param $pjaxName
     * @param $modalName
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
//    public function actionFieldTemplateUpOrderDependent($fieldTemplateId,
//                                                        $pjaxName,
//                                                        $modalName)
//    {
//        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');
//
//        /** @var FieldTemplate $fieldTemplate */
//        $fieldTemplate = FieldTemplate::findOne($fieldTemplateId);
//
//        if (!$fieldTemplate) throw new NotFoundHttpException('Wrong fieldTemplateId');
//
//        $fieldTemplate->upOrder();
//
//        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($fieldTemplate->field_template_reference)
//            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
//            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
//            ->all();
//
//        $fieldTemplatesSingle = FieldTemplate::getListQuery($fieldTemplate->field_template_reference)
//            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
//            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
//            ->all();
//
//        return $this->renderAjax('/pjax/update-input-fields-list-container-dependent', [
//            'fieldTemplateReference'     => $fieldTemplate->field_template_reference,
//            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
//            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
//            'pjaxName'                   => $pjaxName,
//            'modalName'                  => $modalName,
//        ]);
//    }

    /**
     * Action for down field template order in dependent modal window
     * @param $fieldTemplateId
     * @param $pjaxName
     * @param $modalName
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
//    public function actionFieldTemplateDownOrderDependent($fieldTemplateId,
//                                                          $pjaxName,
//                                                          $modalName)
//    {
//        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');
//
//        /** @var FieldTemplate $fieldTemplate */
//        $fieldTemplate = FieldTemplate::findOne($fieldTemplateId);
//
//        if (!$fieldTemplate) throw new NotFoundHttpException('Wrong fieldTemplateId');
//
//        $fieldTemplate->downOrder();
//
//        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($fieldTemplate->field_template_reference)
//            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
//            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
//            ->all();
//
//        $fieldTemplatesSingle = FieldTemplate::getListQuery($fieldTemplate->field_template_reference)
//            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
//            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
//            ->all();
//
//        return $this->renderAjax('/pjax/update-input-fields-list-container-dependent', [
//            'fieldTemplateReference'     => $fieldTemplate->field_template_reference,
//            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
//            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
//            'pjaxName'                   => $pjaxName,
//            'modalName'                  => $modalName,
//        ]);
//    }

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
