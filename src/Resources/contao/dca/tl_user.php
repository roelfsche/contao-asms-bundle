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
			'options_callback'        => array('tl_asms_user', 'getJobOfferUser'),
			// 'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => array('multiple'=>true, 'helpwizard'=>true),
			'sql'                     => "blob NULL"
        );

/**
 * Extend default palette
 */
// $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('email;', 'email;{job_offer_access_legend},job_offer_access;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
// $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('email;', 'email;{job_offer_access_legend},job_offer_access;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);


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

    public function getJobOfferUser() {
        return [
            1 => 'Johann',
            2 => 'Achim'
        ];
    }
}
