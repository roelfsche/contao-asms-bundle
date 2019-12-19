<?php

/**
 * Module
 * Copyright (c) 2014 Stefan Becker
 */

/**
 * Add palettes to tl_module
 */
// $GLOBALS['TL_DCA']['tl_module']['palettes']['Jobdetail']    = '{title_legend},name,type;{config_legend},isHead;{redirect_legend},jumpTo;';
$GLOBALS['TL_DCA']['tl_module']['palettes']['Joblist']    = '{title_legend},name,type;';//{config_legend},isRehab,numberOfItems,perPage,filterClinic;{redirect_legend},jumpTo;{image_legend:hide},imgSize;';


/*
$GLOBALS['TL_DCA']['tl_module']['fields']['isSearchResultInfo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['isSearchResultInfo'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array(),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['isHead'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['isHead'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array(),
    'sql'                     => "char(1) NOT NULL default ''"
);
*/
$GLOBALS['TL_DCA']['tl_module']['fields']['isRehab'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['isRehab'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array(),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['filterClinic'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['filterClinic'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array(),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['jumpToJobResult'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['jumpToJobResult'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);
// $GLOBALS['TL_DCA']['tl_module']['fields']['jobSubject'] = array
// (
//     'label'                   => &$GLOBALS['TL_LANG']['tl_module']['jobSubject'],
//     'exclude'                 => true,
//     'inputType'               => 'select',
//     'foreignKey'              => 'tl_subjects.title',
//     'sql'                     => "int(10) unsigned NOT NULL default '0'",
//     'relation'                => array('type'=>'hasOne', 'load'=>'eager')
// );
