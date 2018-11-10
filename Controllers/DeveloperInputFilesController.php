<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\DevFilter;

/**
 * Class DeveloperInputFilesController
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperInputFilesController extends Controller
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

    public function actionLoadModal($inputFileTemplateId)
    {

    }

    public function actionEmptyModal($inputFileTemplateReference)
    {

    }

    public function actionUpdateInputFilesListContainer($fileTemplateReference)
    {

    }

}
