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

        //         'JobListPerSubject' => 'ModuleJobListPerSubject',
        //         'JobSearchSimple'   => 'ModuleJobSearchSimple',
        //         'JobSearchExtended' => 'ModuleJobSearchExtended',
        //         'JobSearchResult'   => 'ModuleJobSearchResult',
        //         'TabControl'        => 'ModuleTabControl',
        //         'AjaxEvents'        => 'ModuleAjaxEvents'
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
// $GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('VacancyHooks', 'handleIdRedirect');
