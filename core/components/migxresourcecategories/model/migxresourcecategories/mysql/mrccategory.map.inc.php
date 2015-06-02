<?php
$xpdo_meta_map['mrcCategory']= array (
  'package' => 'migxresourcecategories',
  'version' => '1.1',
  'table' => 'mrc_categories',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'name' => '',
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
      'index' => 'index',
    ),
  ),
  'aggregates' => 
  array (
    'Resources' => 
    array (
      'class' => 'mrcResourceCategoryLink',
      'local' => 'id',
      'foreign' => 'category_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
