<?php

/**
 * Stellenangebote
 * Copyright (c) 2014 Stefan Becker
 */

$table = 'tl_jobs';

/**
 * Table tl_jobs
 */
$GLOBALS['TL_DCA'][$table] = array(
    // Config
    'config' => array(
        'dataContainer'               => 'JobTable',
        'onsubmit_callback' => array(
            array($table, 'updateTitle')
        ),
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
            'fields'                  => array('title', 'clinicName'),
            'format'                  => '%s<br/><small>(%s)</small>'
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
            'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_article']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_jobs', 'toggleIcon')
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
        '__selector__'                => array('addAboutUsImage', 'availabilityFrom', /*'individualSubject', */ 'typeLimited', 'addWeOfferImage', 'has_contactperson', 'addLinkButton', 'addLink'),
        // 'default'                     => '{lbl_general},type,clinic,jobID,title,alias,meta_description,subtitle1,subtitle2,titleSelection,subjectSelection,individualSubject;{lbl_contactperson},has_contactperson;{lbl_statement},statementText;{lbl_linkbutton},addLinkButton;{lbl_link},addLink;{lbl_teaser_image},statementImage,statementImageAlt;{lbl_aboutus},aboutUs,addAboutUsImage;{lbl_availability},availabilityFrom;{lbl_type},typeFulltime,typeParttime,typeLimited;{lbl_content},weOffer,addWeOfferImage,youOffer,applicationNotes,individualTextHeadline,individualText;{lbl_published},published,start,stop'
        'default'                     => '{lbl_general},type,clinic,jobID,title,alias,meta_description,subtitle1,subtitle2,titleSelection,subjectSelection;{lbl_contactperson},has_contactperson;{lbl_statement},statementText;{lbl_linkbutton},addLinkButton;{lbl_link},addLink;{lbl_teaser_image},statementImage,statementImageAlt;{lbl_aboutus},aboutUs,addAboutUsImage;{lbl_availability},availabilityFrom;{lbl_type},typeFulltime,typeParttime,typeLimited;{lbl_content},weOffer,addWeOfferImage,youOffer,applicationNotes,individualTextHeadline,individualText;{lbl_published},published,start,stop'
    ),

    // Subpalettes
    'subpalettes' => array(
        'addAboutUsImage'            => 'aboutUsImage,aboutUsImageAlt',
        'addWeOfferImage'            => 'weOfferImage,weOfferImageAlt',
        'availabilityFrom'           => 'availabilityFromDate',
        // 'individualSubject'          => 'subject',
        'typeLimited'                => 'typeLimitedText',
        'has_contactperson'          => 'contactperson_position,contactperson_salutation,contactperson_title,contactperson_firstname,contactperson_lastname,contactperson_email,contactperson_phone',
        'addLinkButton'              => 'linkButtonText,linkButtonTextMobile,linkButtonLink',
        'addLink'                    => 'linkText,linkTextMobile,linkLink'
    ),

    // Fields
    'fields' => array(
        'id' => array(
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array(
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'type' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['type'],
            'default'                 => 'regular',
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array($table, 'getTypes'),
            'eval'                    => array(),
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'titleSelection' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['titleSelection'],
            'inputType'               => 'select',
            'foreignKey'              => 'tl_jobtypes.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type' => 'hasOne', 'load' => 'eager')
        ),
        'individualJobtitle' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['individualJobtitle'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'clinic' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['clinic'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array('tl_jobs', 'getClinics'),
            // 'foreignKey'              => 'tl_clinics.title',
            // 'sql'                     => "int(10) unsigned NOT NULL default '0'",
            // 'eval'                    => array('multiple' => false, 'helpwizard' => true),
            'sql'                     => "blob NULL"
            // 'relation'                => array('type'=>'hasOne', 'load'=>'eager'),
            // 'filter'                  => true
        ),
        'clinicName' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['clinicName'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'jobID' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['jobID'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum', 'disabled' => true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'title' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'alias' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alias'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'search'                  => true,
            'eval'                    => array('rgxp' => 'folderalias', 'doNotCopy' => true, 'maxlength' => 128),
            'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),
        'meta_description' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['meta_description'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'search'                  => true,
            'eval'                    => array('doNotCopy' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'subjectSelection' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['subjectSelection'],
            'inputType'               => 'select',
            'foreignKey'              => 'tl_subjects.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type' => 'hasOne', 'load' => 'eager')
        ),
        'individualSubject' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['individualSubject'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'subject' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['subject'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'subtitle1' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['subtitle1'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => true, 'maxlength' => 160),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'subtitle2' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['subtitle2'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => false, 'maxlength' => 160),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'has_contactperson' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['has_contactperson'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'contactperson_salutation' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_salutation'],
            'default'                 => 'regular',
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array($table, 'getSalutation'),
            'eval'                    => array(),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'contactperson_position' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_position'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_title' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_firstname' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_firstname'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_lastname' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_email' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_email'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_phone' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_phone'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'statementImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['statementImage'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'statementImageAlt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'statementText' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['statementText'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'addLinkButton' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addLinkButton'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'linkButtonText' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['linkButtonText'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'linkButtonTextMobile' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['linkButtonTextMobile'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'linkButtonLink' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['linkButtonLink'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('fieldType' => 'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type' => 'hasOne', 'load' => 'lazy')
        ),
        'addLink' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addLink'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'linkText' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['linkText'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'linkTextMobile' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['linkTextMobile'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'linkLink' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['linkLink'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('fieldType' => 'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type' => 'hasOne', 'load' => 'lazy')
        ),
        'aboutUs' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['aboutUs'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true),
            'sql'                     => "mediumtext NULL"
        ),
        'addAboutUsImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addAboutUsImage'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'aboutUsImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['aboutUsImage'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'aboutUsImageAlt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'availabilityFrom' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['availabilityFrom'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'availabilityFromDate' => array(
            'exclude'                 => true,
            'label'                   => &$GLOBALS['TL_LANG'][$table]['availabilityFromDate'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'typeFulltime' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['typeFulltime'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => false),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'typeParttime' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['typeParttime'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => false),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'typeLimited' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['typeLimited'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'typeLimitedText' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['typeLimitedText'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'weOffer' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['weOffer'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true),
            'sql'                     => "mediumtext NULL"
        ),
        'addWeOfferImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addWeOfferImage'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'weOfferImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['weOfferImage'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'weOfferImageAlt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'youOffer' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['youOffer'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true),
            'sql'                     => "mediumtext NULL"
        ),
        'individualTextHeadline' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['individualTextHeadline'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => false, 'maxlength' => 255),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'individualText' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['individualText'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => false, 'rte' => 'tinyMCE', 'helpwizard' => true),
            'sql'                     => "mediumtext NULL"
        ),
        'applicationNotes' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['applicationNotes'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true),
            'sql'                     => "mediumtext NULL"
        ),
        'start' => array(
            'exclude'                 => true,
            'label'                   => &$GLOBALS['TL_LANG'][$table]['start'],
            'inputType'               => 'text',
            'eval'                    => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'stop' => array(
            'exclude'                 => true,
            'label'                   => &$GLOBALS['TL_LANG'][$table]['stop'],
            'inputType'               => 'text',
            'eval'                    => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'published' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'flag'                    => 1,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);



/**
 * Class tl_jobs
 */
class tl_jobs extends Backend
{
    /**
     * Returns the two types
     * @param \DataContainer
     * @return string
     */
    public function getTypes(DataContainer $dc)
    {
        return array(1 => "Rehabilitationskliniken", 2 => "Sozialmedizinischer Dienst");
    }

    /**
     * Returns all salutations
     * @param \DataContainer
     * @return string
     */
    public function getSalutation(DataContainer $dc)
    {
        $arrOptions = array();

        $arrOptions[] = "Herr";
        $arrOptions[] = "Frau";

        return $arrOptions;
    }

    /**
     * Updates the title field.
     */
    public function updateTitle(DataContainer $dc)
    {
        // Return if there is no ID
        if (!$dc->id) {
            return;
        }

        // Update jobtitle
        /*if ($dc->activeRecord->individualJobtitle == null) {
            $objJobtypesModel = \vacancies\JobtypesModel::findByPk($dc->activeRecord->titleSelection);
            $objJobsModel = \vacancies\JobsModel::findByPk($dc->id);
            $objJobsModel->title = $objJobtypesModel->title;
            $objJobsModel->save();
        }*/

        // Update subject
        if ($dc->activeRecord->individualSubject == null) {
            $objSubjectsModel = \vacancies\SubjectsModel::findByPk($dc->activeRecord->subjectSelection);
            $objJobsModel = \vacancies\JobsModel::findByPk($dc->id);
            $objJobsModel->subject = $objSubjectsModel->title;
            $objJobsModel->save();
        }

        // Update alias
        if ($dc->activeRecord->alias == '') {
            $objJobsModel = \vacancies\JobsModel::findByPk($dc->id);
            $varValue = standardize(String::restoreBasicEntities($objJobsModel->title));
            if (\vacancies\JobsModel::findBy('alias', $varValue)) {
                $objJobsModel->alias = $varValue . '-' . $dc->id;
            } else {
                $objJobsModel->alias = $varValue;
            }

            $objJobsModel->save();
        }

        $types = array('1' => 'REHA', '2' => 'SMD');
        // Update clinic name
        $objJobsModel = \vacancies\JobsModel::findByPk($dc->id);
        $objClinicModel = \vacancies\ClinicsModel::findByPk($objJobsModel->clinic);
        // $objJobsModel->clinicName = $objJobsModel->getRelated('clinic')->title;
        $objJobsModel->clinicName = $objClinicModel->title;
        $objJobsModel->jobID = $types[$objJobsModel->type] . '-' . $dc->id;
        $objJobsModel->save();
    }

    /**
     * Job-Angebote können nach Zuordnung zur Klinik hier eingeschränkt werden
     */
    public function getClinics()
    {
		$this->import('BackendUser', 'User');
        $objUser = $this->User;
        if ($objUser->isAdmin) {
            $arrDbClinics = $this->Database->prepare('SELECT id, title FROM tl_clinics order by title')->execute()->fetchAllAssoc();
        } else {
            $objResult = $this->Database->prepare('SELECT id, title FROM tl_clinics WHERE id in (' . implode(', ', [14, 15, 19]) . ') order by title')->execute();
            $arrDbClinics = $objResult->fetchAllAssoc();
        }
        $arrClinics = [];
        foreach ($arrDbClinics as $arrClinic) {
            $arrClinics[$arrClinic['id']] = $arrClinic['title'];
        }
        return $arrClinics;
    }
    	/**
	 * Return the "toggle visibility" button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		// if (!$this->User->hasAccess('tl_article::published', 'alexf'))
		// {
		// 	return '';
		// }

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}

		// $objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
		// 						  ->limit(1)
		// 						  ->execute($row['pid']);

		// if (!$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objPage->row()))
		// {
		// 	return Image::getHtml($icon) . ' ';
		// }

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
	}


	/**
	 * Disable/enable a user group
	 *
	 * @param integer       $intId
	 * @param boolean       $blnVisible
	 * @param DataContainer $dc
	 */
	public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
	{
		// Set the ID and action
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');

		if ($dc)
		{
			$dc->id = $intId; // see #8043
		}

		// $this->checkPermission();

		// Check the field access
		// if (!$this->User->hasAccess('tl_article::published', 'alexf'))
		// {
		// 	$this->log('Not enough permissions to publish/unpublish article ID "'.$intId.'"', __METHOD__, TL_ERROR);
		// 	$this->redirect('contao/main.php?act=error');
		// }

		// $objVersions = new Versions('tl_article', $intId);
		// $objVersions->initialize();

		// Trigger the save_callback
		// if (is_array($GLOBALS['TL_DCA']['tl_article']['fields']['published']['save_callback']))
		// {
		// 	foreach ($GLOBALS['TL_DCA']['tl_article']['fields']['published']['save_callback'] as $callback)
		// 	{
		// 		if (is_array($callback))
		// 		{
		// 			$this->import($callback[0]);
		// 			$blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
		// 		}
		// 		elseif (is_callable($callback))
		// 		{
		// 			$blnVisible = $callback($blnVisible, ($dc ?: $this));
		// 		}
		// 	}
		// }

		// Update the database
		$this->Database->prepare("UPDATE tl_jobs SET tstamp=". time() .", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
					   ->execute($intId);

		// $objVersions->create();
	}
}
