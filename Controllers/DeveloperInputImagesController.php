<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Base\DevFilter;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsCommon\Base\CommonException;
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
            'dev' => [
                'class' => DevFilter::class,
                'redirect' => function() {
                    return $this->redirect(Url::home());
                }
            ],
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * Action for delete input image block template
     * @param $inputImageBlockId
     * @param bool $deletePass
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteInputImageBlock($inputImageBlockId, $deletePass = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var InputImagesBlock $imagesBlock */
        $inputImagesBlock = InputImagesBlock::findOne($inputImageBlockId);

        if (!$inputImagesBlock) throw new NotFoundHttpException('Wrong inputImageBlockId');

        if ($inputImagesBlock->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new CommonException('Wrong dev password');

        $inputImageTemplateReference = $inputImagesBlock->input_image_template_reference;

        $inputImagesBlock->delete();

        $inputImagesBlocks = InputImagesBlock::getListQuery($inputImageTemplateReference)
            ->orderBy([InputImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/pjax/update-input-images-list-container', [
            'inputImageTemplateReference' => $inputImageTemplateReference,
            'inputImagesBlocks'           => $inputImagesBlocks,
        ]);
    }

    /**
     * Action for up input image block order
     * @param $inputImageBlockId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
