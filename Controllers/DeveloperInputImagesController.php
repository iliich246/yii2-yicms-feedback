<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsFeedback\InputImages\InputImagesBlock;
use Iliich246\YicmsFeedback\InputImages\DevInputImagesGroup;
use Iliich246\YicmsFeedback\InputImages\InputImagesDevModalWidget;

/**
 * Class DeveloperInputImagesController
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperInputImagesController extends Controller
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
     * Action for refresh dev input images modal window
     * @param $inputImageBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionLoadModal($inputImageBlockId)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#' . InputImagesDevModalWidget::getPjaxContainerId()
        ) {
            $devInputImageGroup = new DevInputImagesGroup();
            $devInputImageGroup->initialize($inputImageBlockId);

            return InputImagesDevModalWidget::widget([
                'devInputImagesGroup' => $devInputImageGroup
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for send empty input images modal window
     * @param $inputImageTemplateReference
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionEmptyModal($inputImageTemplateReference)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        $devInputImageGroup = new DevInputImagesGroup();
        $devInputImageGroup->setInputImagesTemplateReference($inputImageTemplateReference);
        $devInputImageGroup->initialize();

        return InputImagesDevModalWidget::widget([
            'devInputImagesGroup' => $devInputImageGroup
        ]);
    }

    /**
     * Action for update images list container
     * @param $inputImageTemplateReference
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionUpdateInputImagesListContainer($inputImageTemplateReference)
    {
        if (Yii::$app->request->isPjax &&
            Yii::$app->request->post('_pjax') == '#update-input-images-list-container'
        ) {
            $inputImagesBlocks = InputImagesBlock::getListQuery($inputImageTemplateReference)
                ->orderBy([InputImagesBlock::getOrderFieldName() => SORT_ASC])
                ->all();

            return $this->render('/pjax/update-input-images-list-container', [
                'inputImageTemplateReference' => $inputImageTemplateReference,
                'inputImagesBlocks'           => $inputImagesBlocks,
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Action for up input image block order
     * @param $inputImageBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputImagesBlockUpOrder($inputImageBlockId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputImagesBlock $inputImageBlock */
        $inputImageBlock = InputImagesBlock::getInstanceById($inputImageBlockId);

        if (!$inputImageBlock) throw new NotFoundHttpException('Wrong inputImageBlockId');

        $inputImageBlock->upOrder();

        $inputImagesBlocks = InputImagesBlock::getListQuery($inputImageBlock->input_image_template_reference)
            ->orderBy([InputImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-images-list-container', [
            'inputImageTemplateReference' => $inputImageBlock->input_image_template_reference,
            'inputImagesBlocks'           => $inputImagesBlocks,
        ]);
    }

    /**
     * Action for down input image block order
     * @param $inputImageBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionInputImagesBlockDownOrder($inputImageBlockId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException('Not Pjax');

        /** @var InputImagesBlock $inputImageBlock */
        $inputImageBlock = InputImagesBlock::getInstanceById($inputImageBlockId);

        if (!$inputImageBlock) throw new NotFoundHttpException('Wrong inputImageBlockId');

        $inputImageBlock->downOrder();

        $inputImagesBlocks = InputImagesBlock::getListQuery($inputImageBlock->input_image_template_reference)
            ->orderBy([InputImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-images-list-container', [
            'inputImageTemplateReference' => $inputImageBlock->input_image_template_reference,
            'inputImagesBlocks'           => $inputImagesBlocks,
        ]);
    }
}
