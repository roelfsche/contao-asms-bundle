<?php

/**
 * Stellenangebote
 * Copyright (c) 2014 Stefan Becker
 */


/**
 * Back end modules
 */
array_insert(
    $GLOBALS['BE_MOD'],
    0,
    array(
        'vacancies' => array(
            /* Stellenangebote */
            'jobs' => array(
                'tables'       => array('tl_jobs')
            ),
            /* Berufstypen */
            'jobtypes' => array(
                'tables'       => array('tl_jobtypes')
            ),
            /* Fachgebiete */
            'subjects' => array(
                'tables'       => array('tl_subjects')
            ),
            /* Stammdaten */
            'clinics' => array(
                'tables'       => array('tl_clinics')
            )
        )
    )
);

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array(
    'vacancies' => array(
        //         'Jobdetail'         => 'ModuleJobdetail',
        'Joblist'           => 'Lumturo\ContaoAsmsBundle\Module\JoblistModule', //'ModuleJoblist',
        'Jobdetails'           => 'Lumturo\ContaoAsmsBundle\Module\JobdetailsModule', //'ModuleJoblist',
        'Jobmap'           => 'Lumturo\ContaoAsmsBundle\Module\JobmapModule', //'ModuleJoblist',
    )
));

/**
 * Content elements
 */
// $GLOBALS['TL_CTE']['texts']['headteaser'] = 'ContentHeadteaser';
// $GLOBALS['TL_CTE']['texts']['headerblock'] = 'ContentHeaderblock';

/**
 * Hooks
 */
// $GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('VacancyHooks', 'loadDC');
// $GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('vacancies\Hooks', 'handleIdRedirect');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('vacancies\FrontendHooks', 'getSearchablePages');

$GLOBALS['TL_MODELS']['tl_subjects'] = 'vacancies\SubjectsModel';
$GLOBALS['TL_MODELS']['tl_jobs'] = 'vacancies\JobsModel';
$GLOBALS['TL_MODELS']['tl_jobtypes'] = 'vacancies\JobtypesModel';
$GLOBALS['TL_MODELS']['tl_clinics'] = 'vacancies\ClinicsModel';
