<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Iliich246\YicmsFeedback\InputConditions\InputConditionValueNamesForm;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\InputConditions\InputConditionValues;
use Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate;
use Iliich246\YicmsFeedback\InputConditions\DevInputConditionsGroup;
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
                'inputConditionTemplates'        => $inputConditionsTemplates,
            ]);
        }

        throw new BadRequestHttpException();
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

        if ($conditionValue->load(Yii::$app->request->post()) &&
            Model::loadMultiple($conditionValuesTranslates, Yii::$app->request->post())) {

            if ($conditionValue->validate() && Model::validateMultiple($conditionValuesTranslates)) {
                $conditionValue->save();

                /** @var ConditionValueNamesForm $conditionValuesTranslate */
                foreach ($conditionValuesTranslates as $conditionValuesTranslate) {
                    $conditionValuesTranslate->save();
                }

                if (Yii::$app->request->post('_saveAndBack'))
                    $returnBack = true;
                else
                    $returnBack = false;

                return $this->renderAjax('/pjax/create-update-condition-value', [
                    'conditionTemplate'         => $conditionTemplate,
                    'conditionValue'            => $conditionValue,
                    'conditionValuesTranslates' => $conditionValuesTranslates,
                    'redirectUpdate'            => true,
                    'returnBack'                => $returnBack
                ]);
            }
        }
    }
}
