<?php

/**
 * User
 * Copyright (c) 2014 Stefan Becker
 */

/**
 * Add palettes to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['__selector__'][] = 'isClinic';

$GLOBALS['TL_DCA']['tl_user']['palettes']['extend']    = str_replace(
    'inherit;',
    'inherit,isClinic;',
    $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']
);
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend']    = str_replace(
    '{forms_legend},forms,formp;',
    '',
    $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']
);
$GLOBALS['TL_DCA']['tl_user']['subpalettes']['isClinic'] = 'clinic';

$GLOBALS['TL_DCA']['tl_user']['fields']['isClinic'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['isClinic'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_user']['fields']['clinic'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['clinic'],
    'inputType'               => 'select',
    'foreignKey'              => 'tl_clinics.title',
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);