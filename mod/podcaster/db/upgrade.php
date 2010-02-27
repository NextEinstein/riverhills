<?php  

// This file keeps track of upgrades to
// the podcaster module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_podcaster_upgrade($oldversion=0) {
    global $CFG, $THEME, $db;
    $result = true;
    if ($result && $oldversion < 2008022501) {
      // allow repositories to be tagged as public 
      $table = new XMLDBTable('podcaster_repository');
      $field = new XMLDBField('public');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'license');
      $result = $result && add_field($table, $field);

 // metachannel table
      $table = new XMLDBTable ('podcaster_metachannel');
      $table->comment = 'Podcaster meta channels';
      $f = $table->addFieldInfo('id', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, NULL, NULL, NULL);
      $f = $table->addFieldInfo('channel', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED, NULL, NULL, NULL, NULL, NULL);
      $f->comment = 'Associated channel';

      $f = $table->addFieldInfo('name', XMLDB_TYPE_CHAR,  '255', NULL, NULL, NULL, NULL, NULL, NULL);
      $f->comment = 'Name of meta channel';

      $f = $table->addFieldInfo('path', XMLDB_TYPE_CHAR,  '255', NULL, NULL, NULL, NULL, NULL, NULL);
      $f->comment = '(Local) path of rss feed';

      $f = $table->addFieldInfo('target', XMLDB_TYPE_CHAR,  '255', NULL, NULL, NULL, true, array('repository','course'), NULL);
      $f->comment = 'Target';

      $f = $table->addFieldInfo('params', XMLDB_TYPE_CHAR,  '255', NULL, NULL, NULL, NULL, NULL, NULL);
      $f->comment = 'Target params, i.e.: csv list of repositories or course ids';

      $f = $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED, NULL, NULL, NULL, NULL, NULL);

      $f = $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED, NULL, NULL, NULL, NULL, NULL);
      $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
      $table->addIndexInfo('target', XMLDB_INDEX_NOTUNIQUE, array('target'));

// Create the table
      $result = $result && create_table($table);
      
// add a meta channel flag to table podcaster
      $table = new XMLDBTable('podcaster');
      $field = new XMLDBField('ismeta');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'course');
      $result = $result && add_field($table, $field);
 
      $field = new XMLDBField('format');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, XMLDB_NOTNULL, NULL, NULL, NULL, '', 'repository');
      $result = $result && change_field_type($table, $field);

// fix some old field definitions
      $table = new XMLDBTable('podcaster_repository');
      $field = new XMLDBField('enabled');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'public');
      $result = $result && change_field_type($table, $field);

      $field = new XMLDBField('isdefault');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'enabled');
      $result = $result && change_field_type($table, $field);

      $field = new XMLDBField('name');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, XMLDB_NOTNULL, NULL, NULL, NULL, '', 'id');
      $result = $result && change_field_type($table, $field);

      $table = new XMLDBTable('podcaster_license');
      $field = new XMLDBField('confirm');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'name');
      $result = $result && change_field_type($table, $field);

      $field = new XMLDBField('isdefault');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'confirm');
      $result = $result && change_field_type($table, $field);

      $field = new XMLDBField('name');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, XMLDB_NOTNULL, NULL, NULL, NULL, '', 'id');
      $result = $result && change_field_type($table, $field);
      
      $table = new XMLDBTable('podcaster_language');
      $field = new XMLDBField('name');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, XMLDB_NOTNULL, NULL, NULL, NULL, '', 'language');
      $result = $result && change_field_type($table, $field);
    }
    if ($result && $oldversion < 2008022502) {
      $table = new XMLDBTable('podcaster');
      $field = new XMLDBField('dirty');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'ismeta');
      $result = $result && add_field($table, $field);

      $table = new XMLDBTable('podcaster_repository');
      $field = new XMLDBField('synchronize');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'public');
      $result = $result && add_field($table, $field);
    }
  
    if ($result && $oldversion < 2008022503) {
      $table = new XMLDBTable('podcaster_item');
      $field = new XMLDBField('enclosureurl');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, NULL, NULL, NULL, NULL, '', 'enclosure');
      $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2008022504) {
      $table = new XMLDBTable('podcaster');
      $field = new XMLDBField('imageurl');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, NULL, NULL, NULL, NULL, '', 'image');
      $result = $result && add_field($table, $field);

      $field = new XMLDBField('imagetype');
      $field->setAttributes(XMLDB_TYPE_CHAR, '255', NULL, NULL, NULL, NULL, NULL, '', 'imageurl');
      $result = $result && add_field($table, $field);

      $field = new XMLDBField('imagelength');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, NULL, NULL, NULL, NULL, '0', 'imagetype');
      $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2008022505) {
      $table = new XMLDBTable('podcaster_repository');
      $field = new XMLDBField('shared');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'public');
      $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2008022506) {
      $table = new XMLDBTable('podcaster');
      $field = new XMLDBField('showpreview');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '1', 'timemodified');
      $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2008022507) {
      $table = new XMLDBTable('podcaster_repository');
      $field = new XMLDBField('rss');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, NULL, NULL, NULL, '0', 'license');
      $result = $result && add_field($table, $field);
    }
    return $result;
} // 
?>
