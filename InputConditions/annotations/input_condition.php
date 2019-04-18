<?php

/** @var $this yii\web\View */
/** @var $annotator \Iliich246\YicmsCommon\Annotations\Annotator */
/** @var $inputConditionTemplate \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate */

$inputConditionTemplate = $annotator->getAnnotatorFileObject();
echo "<?php\n";
?>

namespace <?= $annotator->getNamespace() ?>;

use Yii;
use <?= $annotator->getExtendsUseClass() ?>;


/**
* Class <?= $annotator->getClassName() ?>

*
* This class was generated automatically
*
* @author iliich246 <iliich246@gmail.com>
*/
class <?= $annotator->getClassName() ?> extends <?= $annotator->getExtendsClassName() ?>

{
// |||-> This part of annotation will be change automatically. Do not change it.

// |||<- End of block of auto annotation
}
