<?php

/**
 * Fachgebiete
 * Copyright (c) 2014 Stefan Becker
 */

$table = 'tl_subjects';

/**
 * Table tl_jobs
 */
$GLOBALS['TL_DCA'][$table] = array(
    // Config
    'config' => array(
        'dataContainer'               => 'Table',
        'sql' => array(
            'keys' => array(
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array(
        'sorting' => array(
            'mode'                    => 2,
            'flag'                      => 1,
            'fields'                  => array('title'),
            'format'                  => '%s',
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array(
            'fields'                  => array('title'),
            'format'                  => '%s'
        ),
        'global_operations' => array(
            'all' => array(
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array(
            'edit' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array(
        'default'                     => '{lbl_general},title;{images_legend},img1,img1Alt,img2,img2Alt,img3,img3Alt'
    ),

    // Subpalettes
    'subpalettes' => array(),

    // Fields
    'fields' => array(
        'id' => array(
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array(
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'img1' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['img1'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'img1Alt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'img2' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['img2'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'img2Alt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'img3' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['img3'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'img3Alt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
    )
);
