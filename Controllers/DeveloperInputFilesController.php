<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Base\DevFilter;
use Iliich246\YicmsFeedback\InputFiles\InputFilesBlock;
use Iliich246\YicmsFeedback\InputFiles\DevInputFilesGroup;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;

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

    /**
     * Action for refresh dev input files modal window
     * @param $inputFileBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionLoadModal($inputFileBlockId)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#' . InputFilesDevModalWidget::getPjaxContainerId()
        ) {
            $devInputFileGroup = new DevInputFilesGroup();
            $devInputFileGroup->initialize($inputFileBlockId);

            return InputFilesDevModalWidget::widget([
                'devInputFilesGroup' => $devInputFileGroup
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for send empty input files modal window
     * @param $inputFileTemplateReference
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionEmptyModal($inputFileTemplateReference)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        $devInputFileGroup = new DevInputFilesGroup();
        $devInputFileGroup->setInputFilesTemplateReference($inputFileTemplateReference);
        $devInputFileGroup->initialize();

        return InputFilesDevModalWidget::widget([
            'devInputFilesGroup' => $devInputFileGroup
        ]);
    }

    /**
     * Action for update files list container
     * @param $inputFileTemplateReference
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionUpdateInputFilesListContainer($inputFileTemplateReference)
    {
        //throw new \yii\base\Exception('There');

        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#update-input-files-list-container'
        ) {
            $inputFilesBlocks = InputFilesBlock::getListQuery($inputFileTemplateReference)
                ->orderBy([InputFilesBlock::getOrderFieldName() => SORT_ASC])
                ->all();

            return $this->render('/pjax/update-input-files-list-container', [
                'inputFileTemplateReference' => $inputFileTemplateReference,
                'inputFilesBlocks'           => $inputFilesBlocks,
            ]);
        }

        throw new BadRequestHttpException('penis');
    }

    /**
     * Action for up input file block order
     * @param $inputFileBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputFilesBlockUpOrder($inputFileBlockId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputFilesBlock $inputFileBlock */
        $inputFileBlock = InputFilesBlock::getInstanceById($inputFileBlockId);

        if (!$inputFileBlock) throw new NotFoundHttpException('Wrong inputFieldTemplateId');

        $inputFileBlock->upOrder();

        $inputFileBlocks = InputFilesBlock::getListQuery($inputFileBlock->input_file_template_reference)
            ->orderBy([InputFilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-files-list-container', [
            'inputFileTemplateReference' => $inputFileBlock->input_file_template_reference,
            'inputFileTemplates'         => $inputFileBlocks,
        ]);
    }

    /**
     * Action for down input file block order
     * @param $inputFileBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputFilesBlockDownOrder($inputFileBlockId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputFilesBlock $inputFileBlock */
        $inputFileBlock = InputFilesBlock::getInstanceById($inputFileBlockId);

        if (!$inputFileBlock) throw new NotFoundHttpException('Wrong inputFieldTemplateId');

        $inputFileBlock->downOrder();

        $inputFileBlocks = InputFilesBlock::getListQuery($inputFileBlock->input_file_template_reference)
            ->orderBy([InputFilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-files-list-container', [
            'inputFileTemplateReference' => $inputFileBlock->input_file_template_reference,
            'inputFileTemplates'         => $inputFileBlocks,
        ]);
    }

}
