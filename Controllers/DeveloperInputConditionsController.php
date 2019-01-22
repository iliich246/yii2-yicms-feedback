<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Iliich246\YicmsCommon\Base\CommonException;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsFeedback\Base\FeedbackException;
use Yii;
use yii\base\Model;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\InputConditions\InputConditionValues;
use Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate;
use Iliich246\YicmsFeedback\InputConditions\DevInputConditionsGroup;
use Iliich246\YicmsFeedback\InputConditions\InputConditionValueNamesForm;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

/**
 * Class DeveloperInputConditionsController
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperInputConditionsController extends Controller
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
     * Action for refresh dev input conditions modal window
     * @param $inputConditionTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionLoadModal($inputConditionTemplateId)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#' . InputConditionsDevModalWidget::getPjaxContainerId()
        ) {
            $devInputConditionGroup = new DevInputConditionsGroup();
            $devInputConditionGroup->initialize($inputConditionTemplateId);

            return InputConditionsDevModalWidget::widget([
                'devInputConditionsGroup' => $devInputConditionGroup
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for send empty input conditions modal window
     * @param $inputConditionTemplateReference
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionEmptyModal($inputConditionTemplateReference)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        $devInputConditionGroup = new DevInputConditionsGroup();
        $devInputConditionGroup->setInputConditionsTemplateReference($inputConditionTemplateReference);
        $devInputConditionGroup->initialize();

        return InputConditionsDevModalWidget::widget([
            'devInputConditionsGroup' => $devInputConditionGroup
        ]);
    }

    /**
     * Action for update conditions list container
     * @param $inputConditionTemplateReference
     * @return string
     * @throws BadRequestHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionUpdateInputConditionsListContainer($inputConditionTemplateReference)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#update-input-conditions-list-container'
        ) {
            $inputConditionsTemplates = InputConditionTemplate::getListQuery($inputConditionTemplateReference)
                ->orderBy([InputConditionTemplate::getOrderFieldName() => SORT_ASC])
                ->all();

            return $this->render('/pjax/update-input-conditions-list-container', [
                'inputConditionTemplateReference' => $inputConditionTemplateReference,
                'inputConditionTemplates'         => $inputConditionsTemplates,
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for delete input conditions template
     * @param $inputConditionTemplateId
     * @param bool|false $deletePass
     * @return string
     * @throws BadRequestHttpException
     * @throws FeedbackException
     * @throws NotFoundHttpException
     */
    public function actionDeleteInputConditionTemplate($inputConditionTemplateId, $deletePass = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionTemplate $inputConditionsTemplate */
        $inputConditionsTemplate = InputConditionTemplate::findOne($inputConditionTemplateId);

        if (!$inputConditionsTemplate) throw new NotFoundHttpException('Wrong inputConditionTemplateId');

        if ($inputConditionsTemplate->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new FeedbackException('Wrong dev password');

        $inputConditionTemplateReference = $inputConditionsTemplate->input_condition_template_reference;

        $inputConditionsTemplate->delete();

        $inputConditionsTemplates = InputConditionTemplate::getListQuery($inputConditionTemplateReference)
            ->orderBy([InputConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-conditions-list-container', [
            'inputConditionTemplateReference' => $inputConditionTemplateReference,
            'inputConditionTemplates'         => $inputConditionsTemplates,
        ]);
    }

    /**
     * Action for up input condition template order
     * @param $inputConditionTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionInputConditionsTemplateUpOrder($inputConditionTemplateId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputConditionTemplate $inputConditionsTemplate */
        $inputConditionsTemplate = InputConditionTemplate::getInstanceById($inputConditionTemplateId);

        if (!$inputConditionsTemplate) throw new NotFoundHttpException('Wrong inputConditionTemplateId');

        $inputConditionsTemplate->upOrder();

        $inputConditionsTemplates = InputConditionTemplate::getListQuery($inputConditionsTemplate->input_condition_template_reference)
            ->orderBy([InputConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-conditions-list-container', [
            'inputConditionTemplateReference' => $inputConditionsTemplate->input_condition_template_reference,
            'inputConditionTemplates'         => $inputConditionsTemplates,
        ]);
    }

    /**
     * Action for down input condition template order
     * @param $inputConditionTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionInputConditionsTemplateDownOrder($inputConditionTemplateId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputConditionTemplate $inputConditionsTemplate */
        $inputConditionsTemplate = InputConditionTemplate::getInstanceById($inputConditionTemplateId);

        if (!$inputConditionsTemplate) throw new NotFoundHttpException('Wrong inputConditionTemplateId');

        $inputConditionsTemplate->downOrder();

        $inputConditionsTemplates = InputConditionTemplate::getListQuery($inputConditionsTemplate->input_condition_template_reference)
            ->orderBy([InputConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-conditions-list-container', [
            'inputConditionTemplateReference' => $inputConditionsTemplate->input_condition_template_reference,
            'inputConditionTemplates'         => $inputConditionsTemplates,
        ]);
    }

    /**
     * Returns list of input condition values
     * @param $inputConditionTemplateId
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionInputConditionValuesList($inputConditionTemplateId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionTemplate $inputConditionsTemplate */
        $inputConditionsTemplate = InputConditionTemplate::getInstanceById($inputConditionTemplateId);

        $inputConditionValues = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $inputConditionsTemplate->id,
        ])->orderBy([
            'input_condition_value_order' => SORT_ASC
        ])->all();

        return $this->renderAjax('/pjax/input-conditions-value-list-container', [
            'inputConditionTemplate' => $inputConditionsTemplate,
            'inputConditionValues'   => $inputConditionValues
        ]);
    }

    /**
     * Action for create input condition value
     * @param $inputConditionTemplateId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionCreateInputConditionValue($inputConditionTemplateId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionTemplate $inputConditionTemplate */
        $inputConditionTemplate = InputConditionTemplate::getInstanceById($inputConditionTemplateId);

        if (!$inputConditionTemplate) throw new NotFoundHttpException('Wrong inputConditionTemplateId');

        $inputConditionValue = new InputConditionValues();
        $inputConditionValue->scenario = InputConditionValues::SCENARIO_CREATE;
        $inputConditionValue->setInputConditionTemplate($inputConditionTemplate);

        $languages = Language::getInstance()->usedLanguages();

        $inputConditionValuesTranslates = [];

        foreach($languages as $key => $language) {

            $inputConditionValuesTranslate = new InputConditionValueNamesForm();
            $inputConditionValuesTranslate->scenario = InputConditionValueNamesForm::SCENARIO_CREATE;
            $inputConditionValuesTranslate->setLanguage($language);
            $inputConditionValuesTranslate->setInputConditionValues($inputConditionValue);

            $inputConditionValuesTranslates[$key] = $inputConditionValuesTranslate;
        }

        if ($inputConditionValue->load(Yii::$app->request->post()) &&
            Model::loadMultiple($inputConditionValuesTranslates, Yii::$app->request->post())) {

            if ($inputConditionValue->validate() && Model::validateMultiple($inputConditionValuesTranslates)) {
                $inputConditionValue->save();

                /** @var InputConditionValueNamesForm $inputConditionValuesTranslate */
                foreach ($inputConditionValuesTranslates as $inputConditionValuesTranslate) {
                    $inputConditionValuesTranslate->save();
                }

                if (Yii::$app->request->post('_saveAndBack'))
                    $returnBack = true;
                else
                    $returnBack = false;

                return $this->renderAjax('/pjax/create-update-input-condition-value', [
                    'inputConditionTemplate'         => $inputConditionTemplate,
                    'inputConditionValue'            => $inputConditionValue,
                    'inputConditionValuesTranslates' => $inputConditionValuesTranslates,
                    'redirectUpdate'                 => true,
                    'returnBack'                     => $returnBack
                ]);
            }
        }

        return $this->renderAjax('/pjax/create-update-input-condition-value', [
            'inputConditionTemplate'         => $inputConditionTemplate,
            'inputConditionValue'            => $inputConditionValue,
            'inputConditionValuesTranslates' => $inputConditionValuesTranslates,
        ]);
    }

    /**
     * Action for update input condition value
     * @param $inputConditionValueId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionUpdateInputConditionValue($inputConditionValueId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionValues $inputConditionValue */
        $inputConditionValue = InputConditionValues::findOne($inputConditionValueId);

        if (!$inputConditionValue) throw new NotFoundHttpException('Wrong $inputConditionValueId');

        $inputConditionTemplate = $inputConditionValue->getInputConditionTemplate();

        $inputConditionValue->scenario = InputConditionValues::SCENARIO_UPDATE;

        $languages = Language::getInstance()->usedLanguages();

        $inputConditionValuesTranslates = [];

        foreach($languages as $key => $language) {

            $inputConditionValuesTranslate = new InputConditionValueNamesForm();
            $inputConditionValuesTranslate->scenario = InputConditionValueNamesForm::SCENARIO_CREATE;
            $inputConditionValuesTranslate->setLanguage($language);
            $inputConditionValuesTranslate->setInputConditionValues($inputConditionValue);

            $inputConditionValuesTranslates[$key] = $inputConditionValuesTranslate;
        }

        if ($inputConditionValue->load(Yii::$app->request->post()) &&
            Model::loadMultiple($inputConditionValuesTranslates, Yii::$app->request->post())) {

            if ($inputConditionValue->validate() && Model::validateMultiple($inputConditionValuesTranslates)) {
                $inputConditionValue->save();

                /** @var InputConditionValueNamesForm $inputConditionValuesTranslate */
                foreach ($inputConditionValuesTranslates as $inputConditionValuesTranslate) {
                    $inputConditionValuesTranslate->save();
                }

                if (Yii::$app->request->post('_saveAndBack'))
                    $returnBack = true;
                else
                    $returnBack = false;

                return $this->renderAjax('/pjax/create-update-input-condition-value', [
                    'inputConditionTemplate'         => $inputConditionTemplate,
                    'inputConditionValue'            => $inputConditionValue,
                    'inputConditionValuesTranslates' => $inputConditionValuesTranslates,
                    'returnBack'                     => $returnBack
                ]);
            }
        }

        return $this->renderAjax('/pjax/create-update-input-condition-value', [
            'inputConditionTemplate'         => $inputConditionTemplate,
            'inputConditionValue'            => $inputConditionValue,
            'inputConditionValuesTranslates' => $inputConditionValuesTranslates,
        ]);
    }

    /**
     * Delete selected input condition value
     * @param $inputConditionValueId
     * @param bool|false $deletePass
     * @return string
     * @throws BadRequestHttpException
     * @throws FeedbackException
     * @throws NotFoundHttpException
     */
    public function actionDeleteInputConditionValue($inputConditionValueId, $deletePass = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionValues $inputConditionValue */
        $inputConditionValue = InputConditionValues::findOne($inputConditionValueId);

        if (!$inputConditionValue) throw new NotFoundHttpException('Wrong inputConditionValueId');

        if ($inputConditionValue->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new FeedbackException('Wrong dev password');

        $inputConditionTemplate = $inputConditionValue->getInputConditionTemplate();

        $inputConditionValue->delete();

        $inputConditionValues = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $inputConditionTemplate->id,
        ])->orderBy([
            'input_condition_value_order' => SORT_ASC
        ])->all();

        return $this->renderAjax('/pjax/input-conditions-value-list-container', [
            'inputConditionTemplate' => $inputConditionTemplate,
            'inputConditionValues'   => $inputConditionValues
        ]);
    }

    /**
     * Up input condition value order
     * @param $inputConditionValueId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputConditionValueUpOrder($inputConditionValueId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionValues $inputConditionValue */
        $inputConditionValue = InputConditionValues::findOne($inputConditionValueId);

        if (!$inputConditionValue) throw new NotFoundHttpException('Wrong inputConditionValueId');

        $inputConditionTemplate = $inputConditionValue->getInputConditionTemplate();

        $inputConditionValue->upOrder();

        $inputConditionValues = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $inputConditionTemplate->id,
        ])->orderBy([
            'input_condition_value_order' => SORT_ASC
        ])->all();

        return $this->renderAjax('/pjax/input-conditions-value-list-container', [
            'inputConditionTemplate' => $inputConditionTemplate,
            'inputConditionValues'   => $inputConditionValues
        ]);
    }

    /**
     * Down input condition value order
     * @param $inputConditionValueId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputConditionValueDownOrder($inputConditionValueId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputConditionValues $inputConditionValue */
        $inputConditionValue = InputConditionValues::findOne($inputConditionValueId);

        if (!$inputConditionValue) throw new NotFoundHttpException('Wrong inputConditionValueId');

        $inputConditionTemplate = $inputConditionValue->getInputConditionTemplate();

        $inputConditionValue->downOrder();

        $inputConditionValues = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $inputConditionTemplate->id,
        ])->orderBy([
            'input_condition_value_order' => SORT_ASC
        ])->all();

        return $this->renderAjax('/pjax/input-conditions-value-list-container', [
            'inputConditionTemplate' => $inputConditionTemplate,
            'inputConditionValues'   => $inputConditionValues
        ]);
    }
}
