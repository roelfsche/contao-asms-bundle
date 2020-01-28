<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */
/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes
    'vacancies\Hooks'                   => 'vendor/lumturo/contao-asms-bundle/src/Resources/contao/classes/Hooks.php',
    // Drivers
    'Contao\DC_JobTable'                => 'vendor/lumturo/contao-asms-bundle/src/Resources/contao/drivers/DC_JobTable.php',
    // Models
    'vacancies\JobtypesModel'           => 'vendor/lumturo/contao-asms-bundle/src/Resources/contao/models/JobtypesModel.php',
	'vacancies\ClinicsModel'            => 'vendor/lumturo/contao-asms-bundle/src/Resources/contao/models/ClinicsModel.php',
	'vacancies\JobsModel'               => 'vendor/lumturo/contao-asms-bundle/src/Resources/contao/models/JobsModel.php',
	'vacancies\SubjectsModel'           => 'vendor/lumturo/contao-asms-bundle/src/Resources/contao/models/SubjectsModel.php',
));

