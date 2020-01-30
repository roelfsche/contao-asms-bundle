<?php
/**
 * Created by PhpStorm.
 * User: rolf
 * Date: 01.02.18
 * Time: 20:52
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{asms_config},detailsPage;';

/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_settings']['fields']['detailsPage'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['detailsPage'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
);
