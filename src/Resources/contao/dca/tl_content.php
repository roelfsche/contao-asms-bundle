<?php

/**
 * Module
 * Copyright (c) 2014 Stefan Becker
 */
$table = 'tl_content';

/**
 * Add palettes to tl_content
 */
array_push($GLOBALS['TL_DCA'][$table]['palettes']['__selector__'], 'addLinkButton');
array_push($GLOBALS['TL_DCA'][$table]['palettes']['__selector__'], 'addLink');
array_push($GLOBALS['TL_DCA'][$table]['palettes']['__selector__'], 'addFileLink');
$GLOBALS['TL_DCA'][$table]['palettes']['headerblock'] = '{lbl_general},name,type,headerblock_title1,headerblock_title2,headerblock_title3';
$GLOBALS['TL_DCA'][$table]['palettes']['headteaser'] = '{lbl_general},name,type,hiddentitle,cite;{lbl_linkbutton},addLinkButton;{lbl_link},addLink;{lbl_filelink},addFileLink;{lbl_image},headteaserImage,headteaserImageAlt';
$GLOBALS['TL_DCA'][$table]['subpalettes']['addLinkButton'] = 'linkButtonText,linkButtonTextMobile,linkButtonLink';
$GLOBALS['TL_DCA'][$table]['subpalettes']['addLink'] = 'linkText,linkTextMobile,linkLink';
$GLOBALS['TL_DCA'][$table]['subpalettes']['addFileLink'] = 'fileLinkText,fileLinkTextMobile,fileLinkLink';

/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA'][$table]['fields']['headerblock_title1'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['headerblock_title1'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['headerblock_title2'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['headerblock_title2'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['headerblock_title3'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['headerblock_title3'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['hiddentitle'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['hiddentitle'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['addLinkButton'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['addLinkButton'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['linkButtonText'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['linkButtonText'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['linkButtonTextMobile'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['linkButtonTextMobile'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['linkButtonLink'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['linkButtonLink'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);
$GLOBALS['TL_DCA'][$table]['fields']['cite'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['cite'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'textarea',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>200),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['addLink'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['addLink'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['linkText'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['linkText'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['linkTextMobile'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['linkTextMobile'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['linkLink'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['linkLink'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);

$GLOBALS['TL_DCA'][$table]['fields']['addFileLink'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['addFileLink'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['fileLinkText'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['fileLinkText'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['fileLinkTextMobile'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['fileLinkTextMobile'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][$table]['fields']['fileLinkLink'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['fileLinkLink'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
    'sql'                     => "binary(16) NULL"
);

$GLOBALS['TL_DCA'][$table]['fields']['headteaserImage'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['headteaserImage'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
    'sql'                     => "binary(16) NULL"
);
$GLOBALS['TL_DCA'][$table]['fields']['headteaserImageAlt'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);