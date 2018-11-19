<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

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

    public function actionLoadModal($inputConditionTemplateId)
    {

    }

    public function actionEmptyModal($inputConditionTemplateReference)
    {

    }

    public function actionUpdateConditionImagesListContainer($inputConditionTemplateReference)
    {

    }

    public function actionInputConditionsTemplateUpOrder($inputConditionTemplateId)
    {

    }

    public function actionInputConditionsTemplateDownOrder($inputConditionTemplateId)
    {

    }


}
