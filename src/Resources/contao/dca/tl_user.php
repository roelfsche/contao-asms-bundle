<?php

/**
 * User
 * Copyright (c) 2014 Stefan Becker
 */

/**
 * Add palettes to tl_user
 */

$GLOBALS['TL_DCA']['tl_user']['fields']['job_offer_access'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['job_offer_access'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options_callback'        => array('tl_asms_user', 'getClinics'),
			// 'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => array('multiple'=>true, 'helpwizard'=>true),
			'sql'                     => "blob NULL"
        );

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['default'] = str_replace('email;', 'email;{job_offer_access_legend},job_offer_access;', $GLOBALS['TL_DCA']['tl_user']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('email;', 'email;{job_offer_access_legend},job_offer_access;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('email;', 'email;{job_offer_access_legend},job_offer_access;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['group'] = str_replace('email;', 'email;{job_offer_access_legend},job_offer_access;', $GLOBALS['TL_DCA']['tl_user']['palettes']['group']);


class tl_asms_user extends Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Job-Angebote können nach Zuordnung zur Klinik hier eingeschränkt werden
     */
    public function getClinics() {
        $arrDbClinics = $this->Database->prepare('SELECT id, title FROM tl_clinics order by title')->execute()->fetchAllAssoc();
        $arrClinics = [];
        foreach($arrDbClinics as $arrClinic) {
            $arrClinics[$arrClinic['id']] = $arrClinic['title'];
        }
        return $arrClinics;
    }
}
