<?php
$config = $modx->migx->customconfigs;

$prefix = null;
$packageName = 'migxResourceCategories';

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);

$joinclass = 'mrcResourceCategoryLink';
$resource_id = (int) $modx->getOption('resource_id',$_REQUEST,0);
$object_id = (int) $modx->getOption('object_id',$_REQUEST,0);

$c = $modx->newQuery($joinclass);
$c->where(array('category_id'=>$object_id));
$c->sortby('pos');

$output = array();
if ($collection = $modx->getCollection($joinclass,$c)){
    foreach ($collection as $object){
        $output[] = $object->get('pos');
    }
}

return implode('||',$output);