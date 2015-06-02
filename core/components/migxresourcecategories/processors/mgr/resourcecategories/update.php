<?php

/**
 * MIGXdb
 *
 * Copyright 2012 by Bruno Perner <b.perner@gmx.de>
 *
 * This file is part of MIGXdb, for editing custom-tables in MODx Revolution CMP.
 *
 * MIGXdb is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MIGXdb is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MIGXdb; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA 
 *
 * @package migx
 */
/**
 * Update and Create-processor for migxdb
 *
 * @package migx
 * @subpackage processors
 */
//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));

//return $modx->error->failure('huhu');

if (empty($scriptProperties['object_id'])) {
    $updateerror = true;
    $errormsg = $modx->lexicon('quip.thread_err_ns');
    return;
}
$errormsg = '';
$config = $modx->migx->customconfigs;

$hooksnippets = $modx->fromJson($modx->getOption('hooksnippets', $config, ''));
if (is_array($hooksnippets)) {
    $hooksnippet_aftersave = $modx->getOption('aftersave', $hooksnippets, '');
}

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);

    if (count($packageNames) == '1') {
        //for now connecting also to foreign databases, only with one package by default possible
        $xpdo = $modx->migx->getXpdoInstanceAndAddPackage($config);
    } else {
        //all packages must have the same prefix for now!
        foreach ($packageNames as $packageName) {
            $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
            $modelpath = $packagepath . 'model/';
            if (is_dir($modelpath)) {
                $modx->addPackage($packageName, $modelpath, $prefix);
            }

        }
        $xpdo = &$modx;
    }
} else {
    $xpdo = &$modx;
}

$classname = $config['classname'];

$auto_create_tables = isset($config['auto_create_tables']) ? $config['auto_create_tables'] : true;
$modx->setOption(xPDO::OPT_AUTO_CREATE_TABLES, $auto_create_tables);

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

$co_id = $modx->getOption('co_id', $scriptProperties, '');

if (isset($scriptProperties['data'])) {
    $scriptProperties = array_merge($scriptProperties, $modx->fromJson($scriptProperties['data']));
}

$resource_id = $modx->getOption('resource_id', $scriptProperties, false);
$resource_id = !empty($co_id) ? $co_id : $resource_id;


$modx->migx->loadConfigs();
$tabs = $modx->migx->getTabs();
$form_fields = $modx->migx->extractFieldsFromTabs($tabs);


if ($scriptProperties['object_id'] == 'new') {
    //Add new Category
    $object = $xpdo->newObject($classname);
    $object->fromArray($scriptProperties);
    $object->save();

} else {
    //update positions, if joined
    $resource_id = $modx->getOption('resource_id', $scriptProperties, 0);
    $object_id = $modx->getOption('object_id', $scriptProperties, 0);

    $joinclass = 'mrcResourceCategoryLink';

    $c = $modx->newQuery($joinclass);
    $c->where(array('category_id' => $object_id, 'resource_id' => $resource_id));
    if ($object = $modx->getObject($joinclass, $c)) {
        $oldpos = $object->get('pos');
        $newpos = $modx->getOption('pos', $scriptProperties, 0);
        if ($newpos <> $oldpos) {

            $c = $modx->newQuery($joinclass);
            $c->where(array('category_id' => $object_id));
            $c->sortby('pos');
            $c->prepare();
            //echo $c->toSql();
            if ($collection = $modx->getCollection($joinclass, $c)) {
                $pos = 1;
                foreach ($collection as $joinobject) {

                    $id = $joinobject->get('resource_id');
                    if ($pos == $newpos) {
                        $object->set('pos', $pos);
                        $object->save();
                        $pos++;
                    }
                    if ($id != $resource_id) {
                        $joinobject->set('pos', $pos);
                        $joinobject->save();
                        $pos++;
                    }
                }

                if ($newpos >= $pos) {
                    $object->set('pos', $newpos);
                    $object->save();
                }
            }
        }
    }
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

?>
