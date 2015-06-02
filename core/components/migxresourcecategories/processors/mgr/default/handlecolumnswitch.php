<?php

$config = $modx->migx->customconfigs;

$prefix = $modx->getOption('prefix', $config, null);
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

$joinclass = 'mrcResourceCategoryLink';
$resource_id = $modx->getOption('resource_id', $scriptProperties, 0);
$object_id = $modx->getOption('object_id', $scriptProperties, 0);

switch ($scriptProperties['idx']) {
    case '1':

        //get max position
        $resCategories = array();
        $positions = array();
        $c = $modx->newQuery($joinclass);
        $c->where(array('category_id' => $object_id));
        $c->sortby('pos','DESC');
        $c->limit('1');
        $c->prepare();
        //echo $c->toSql();
        $pos = 0;
        if ($object = $modx->getObject($joinclass, $c)) {
            $pos = $object->get('pos');
        }

        $pos = $pos + 1;

        if ($joinobject = $modx->getObject($joinclass, array('resource_id' => $resource_id, 'category_id' => $object_id))) {

        } else {
            $joinobject = $modx->newObject($joinclass);
            $joinobject->set('pos', $pos);
            $joinobject->set('resource_id', $resource_id);
            $joinobject->set('category_id', $object_id);
        }
        $joinobject->save();
        break;
    case '0':
        if ($joinobject = $modx->getObject($joinclass, array('resource_id' => $resource_id, 'category_id' => $object_id))) {
            $joinobject->remove();
            
            $c = $modx->newQuery($joinclass);
            $c->where(array('category_id' => $object_id));
            $c->sortby('pos');
            $c->prepare();
            //echo $c->toSql();
            $pos = 0;
            if ($collection = $modx->getCollection($joinclass, $c)) {
                $pos = 1;
                foreach ($collection as $object){
                    $object->set('pos',$pos);
                    $object->save();
                    $pos ++;
                }
            }
        }
        break;
    default:
        break;
}


//clear cache for all contexts
$collection = $modx->getCollection('modContext');
foreach ($collection as $context) {
    $contexts[] = $context->get('key');
}
$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));

return $modx->error->success();
