<?php

namespace Lumturo\ContaoAsmsBundle\Module;

use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Patchwork\Utf8;

class JoblistModule extends \Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_joblist';

    /**
     * enthält den Fachbereich, falls einer am Modul eingestellt
     */
    protected $intSubjectFilterValue = 0;
    /**
     * enthält den typ (1/2 => Reha/Soz.Med.Dienst), falls einer am Modul eingestellt
     */
    protected $intTypeFilterValue = 0;

    /**
     * Displays a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        $this->intSubjectFilterValue = (int) $this->subjects;
        $this->intTypeFilterValue = (int) $this->job_type;

        if (TL_MODE == 'BE') {
            $template = new \BackendTemplate('be_wildcard');

            $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['Joblist'][0]) . ' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        } else {
            // if ($this->intSubjectFilterValue == 0) {
            // $GLOBALS['TL_JAVASCRIPT'][] = 'https://code.jquery.com/jquery-3.4.1.min.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaoasms/js/list.min.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaoasms/js/joblist.js';
            // }
        }

        return parent::generate();
    }

    /**
     * Generates the module.
     */
    protected function compile()
    {
        $intTime = time();
        // keine Modul-Filter-Config...
        if (!($this->intSubjectFilterValue || $this->intTypeFilterValue)) {
            $this->Template->show_filter = true;
            // selektiere alle Job-Typen für Filter
            $arrShortJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE a.showInLinkList = 1 AND b.published=\'1\' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?);')->execute($intTime, $intTime)->fetchAllAssoc();
            $arrJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE b.published=\'1\' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?);')->execute($intTime, $intTime)->fetchAllAssoc();
            // $arrJobFields = $this->Database->prepare('select distinct a.id, a.title from tl_subjects a join tl_jobs b on (a.id = b.subjectSelection) WHERE b.published=\'1\' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?) ORDER BY a.title;')->execute($intTime, $intTime)->fetchAllAssoc();
            $strSQL = 'select
            a.id,
            a.jobID as jobId,
            a.alias as jobAlias,
            a.typeFulltime, a.typeParttime, a.typeLimited, a.weOffer, a.youOffer, a.applicationNotes, a.aboutUs,
            a.titleSelection as jobType,
            a.subjectSelection as jobSubject,
            a.has_contactperson,
            a.contactperson_salutation,
            a.contactperson_position,
            a.contactperson_title ,
            a.contactperson_firstname,
            a.contactperson_lastname,
            a.contactperson_phone,
            a.contactperson_email,
            a.subjectImage,
            a.subjectImageAlt,
            b.title as clinicTitle,
            b.city,
            b.city as city2,
            b.logo as clinicLogo,
            b.logoAlt as clinicLogoAlt,
            b.zipCode,
            b.optionalImage,
            b.optionalImageAlt,
            b.lat,
            b.lon,
            b.contactperson_salutation as clinic_contactperson_salutation,
            \'\' as clinic_contactperson_position,
            b.contactperson_title as clinic_contactperson_title,
            b.contactperson_firstname as clinic_contactperson_firstname,
            b.contactperson_lastname  as clinic_contactperson_lastname,
            b.contactperson_phone as clinic_contactperson_phone,
            b.contactperson_email as clinic_contactperson_email,
            b.department,
            b.street,
            b.houseNumber,
            b.zipCode,
            b.url1,
            b.url2,
            b.clinicPDF,
            b.addAwardImage1,
            b.awardImage1,
            b.awardImage1Alt,
            b.addAwardImage2,
            b.awardImage2,
            b.awardImage2Alt,
            b.equality,
            c.title as jobTitle, c.title as jobTitle2
            from tl_jobs a
            join tl_clinics b on (a.clinic = b.id)
            join tl_jobtypes c on (a.titleSelection = c.id)
            where a.published = \'1\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?)';
            if (($strCity = $this->Input->get('city')) != '') {
                $strSQL .= " AND b.city = ?";
                $objResult = $this->Database->prepare($strSQL)->execute($intTime, $intTime, $strCity);
                $arrJobs = $objResult->fetchAllAssoc();
            } else {
                $arrJobs = $this->Database->prepare($strSQL)->execute($intTime, $intTime)->fetchAllAssoc();
            }
        } else {
            // selektiere nach Modul-Filter: Typ / Subject
            // $strSql = 'select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE a.showInLinkList = 1 AND b.published=\'1\'';
            $arrParams = [$intTime, $intTime];
            $strFilterSql = '';
            // if ($this->intSubjectFilterValue) {
            // $strFilterSql .= ' AND b.subjectSelection=?';
            // $arrParams[] = $this->intSubjectFilterValue;
            // }
            if ($this->intTypeFilterValue) {
                $strFilterSql .= ' AND b.type=?';
                $arrParams[] = $this->intTypeFilterValue;
            }

            $arrShortJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE a.showInLinkList = 1 AND b.published=\'1\' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?)' . $strFilterSql)->execute($arrParams)->fetchAllAssoc();

            $arrJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE b.published=\'1\' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?)' . $strFilterSql)->execute($arrParams)->fetchAllAssoc();
            // $arrJobFields = $this->Database->prepare('select distinct a.id, a.title from tl_subjects a join tl_jobs b on (a.id = b.subjectSelection) WHERE b.published=\'1\' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?)' . $strFilterSql . ' ORDER BY a.title;')->execute($arrParams)->fetchAllAssoc();
            $strSQL = 'select
                        b.id,
                        b.jobID as jobId,
                        b.alias as jobAlias,
                        b.typeFulltime, b.typeParttime, b.typeLimited, b.weOffer, b.youOffer, b.applicationNotes, b.aboutUs,
                        b.titleSelection as jobType,
                        b.subjectSelection as jobSubject,
                        b.contactperson_salutation,
                        b.contactperson_position,
                        b.contactperson_title ,
                        b.contactperson_firstname,
                        b.contactperson_lastname,
                        b.contactperson_phone,
                        b.contactperson_email,
                        b.subjectImage,
                        b.subjectImageAlt,
                        a.title as clinicTitle,
                        a.city,
                        a.city as city2,
                        a.logo as clinicLogo,
                        a.logoAlt as clinicLogoAlt,
                        a.zipCode,
                        a.optionalImage,
                        a.optionalImageAlt,
                        a.lat,
                        a.lon,
                        a.contactperson_salutation as clinic_contactperson_salutation,
                        \'\' as clinic_contactperson_position,
                        a.contactperson_title as clinic_contactperson_title,
                        a.contactperson_firstname as clinic_contactperson_firstname,
                        a.contactperson_lastname  as clinic_contactperson_lastname,
                        a.contactperson_phone as clinic_contactperson_phone,
                        a.contactperson_email as clinic_contactperson_email,
                        a.department,
                        a.street,
                        a.houseNumber,
                        a.zipCode,
                        a.url1,
                        a.url2,
                        a.clinicPDF,
                        a.addAwardImage1,
                        a.awardImage1,
                        a.awardImage1Alt,
                        a.addAwardImage2,
                        a.awardImage2,
                        a.awardImage2Alt,
                        a.equality,
                        c.title as jobTitle, c.title as jobTitle2
                        from tl_jobs b
                        join tl_clinics a on (b.clinic = a.id)
                        join tl_jobtypes c on (b.titleSelection = c.id)
                        where b.published = \'1\'' . ' AND (b.start = \'\' or b.start < ?) AND (b.stop = \'\' or b.stop > ?)' . $strFilterSql;
// $strSQL .= ' AND b.jobId in (64, 608)';

            if (($strCity = $this->Input->get('city')) != '') {
                $strSQL .= ' AND b.city = ?';
                $arrParams[] = $strCity;
                $objResult = $this->Database->prepare($strSQL)->execute($arrParams);
                $arrJobs = $objResult->fetchAllAssoc();
            } else {
                $arrJobs = $this->Database->prepare($strSQL)->execute($arrParams)->fetchAllAssoc();
            }

            $this->Template->show_filter = false;
        }

        // Fach ist nun als json-array am Objket
        // mache daraus erstmal ein Array und filtere den Job raus, wenn nicht zugehörig
        $arrSubjectIds = array(); // merke mir auch alle zugeordneten Fächer
        foreach ($arrJobs as $intIndex => $arrJob) {
            $intSubjectId = (int) $arrJob['jobSubject'];
            if ($intSubjectId) {
                $arrSubjects = array($intSubjectId);
                $arrSubjectIds[$intSubjectId] = $intSubjectId;
            } else {
                $arrSubjects = \Contao\StringUtil::deserialize($arrJob['jobSubject']);
            }
            $arrJob['subjects'] = $arrSubjects;
            if ($this->intSubjectFilterValue && !in_array($this->intSubjectFilterValue, $arrSubjects)) {
                unset($arrJobs[$intIndex]);
            } else {
                //setze die subject-ids am Job
                $arrJobs[$intIndex] = $arrJob;
                // und merke mir die Id's über alle (für filter)
                foreach ($arrSubjects as $intSubjectId) {
                    $arrSubjectIds[$intSubjectId] = $intSubjectId;
                }
            }
        }
        $arrJobFields = $this->Database->prepare('select a.id, a.title from tl_subjects a WHERE a.id in (' . implode(', ', $arrSubjectIds) . ') ORDER BY a.title;')->execute()->fetchAllAssoc();
        $arrSubjectNames = array();
        foreach ($arrJobFields as $arrSubjectRow) {
            $arrSubjectNames[$arrSubjectRow['id']] = $arrSubjectRow['title'];
        }

        // $strFilterSql .= ' AND b.subjectSelection=?';
        // $arrParams[] = $this->intSubjectFilterValue;
        // }

        // wenn in der Modul-Config eine max_Anzahl hinterlegt, dann werden nur die
        // - aber zufällig - ausgegeben
        if ($this->max_results) {
            $arrJobs = array_slice($arrJobs, 0, $this->max_results);
        }
        shuffle($arrJobs);

        // id -> index; Vollzeit / Teilzeit
        $arrFixedJobs = [];
        foreach ($arrJobs as $intId => $arrJob) {
            // // city aus title raus
            // $arrJob['clinicTitle'] = str_replace(' ' . $arrJob['city'], '', $arrJob['clinicTitle']);
            // Baue für extra Feld zip + City zusammen
            $arrJob['zipCodeCity'] = $arrJob['zipCode'] . ' ' . $arrJob['city'];

            // Subject (Fach) - Namen
            $arrJobSubjectNames = array();
            foreach ($arrJob['subjects'] as $intSubjectId) {
                $arrJobSubjectNames[$intSubjectId] = $arrSubjectNames[$intSubjectId];
            }
            $arrJob['subjectNames'] = $arrJobSubjectNames;
            $arrJob['subjectTitle'] = implode(', ', $arrJobSubjectNames);
            // Subject-Image
            if ($arrJob['subjectImage'] != null) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['subjectImage']);
                if ($objFile) {
                    $arrJob['subjectImage'] = '/' . $objFile->path;
                } else {
                    unset($arrJob['subjectImage']);
                    unset($arrJob['subjectImageAlt']);
                }
            } else {
                unset($arrJob['subjectImage']);
                unset($arrJob['subjectImageAlt']);
            }

            // Logo
            if ($arrJob['clinicLogo'] != null) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['clinicLogo']);
                if ($objFile) {
                    $arrJob['clinicLogo'] = '/' . $objFile->path;
                } else {
                    unset($arrJob['clinicLogo']);
                    unset($arrJob['clinicLogoAlt']);
                }
            } else {
                unset($arrJob['clinicLogo']);
                unset($arrJob['clinicLogoAlt']);
            }

            // Klinikbild
            if ($arrJob['optionalImage'] != null) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['optionalImage']);
                if ($objFile) {
                    $arrJob['optionalImage'] = '/' . $objFile->path;
                } else {
                    unset($arrJob['optionalImage']);
                    unset($arrJob['optionalImageAlt']);
                }
            } else {
                unset($arrJob['optionalImage']);
                unset($arrJob['optionalImageAlt']);
            }

            if ($arrJob['typeFulltime'] == 1 && $arrJob['typeParttime'] == 1) {
                unset($arrJob['typeFulltime']);
                unset($arrJob['typeParttime']);
                $arrJob['typeFullParttime'] = 'Voll-/Teilzeit';
            }
            $arrJob['typeFulltime'] = ($arrJob['typeFulltime'] == 1) ? 'Vollzeit' : '';
            $arrJob['typeParttime'] = ($arrJob['typeParttime'] == 1) ? 'Teilzeit' : '';
            $arrJob['typeLimited'] = ($arrJob['typeLimited'] == 1) ? 'Befristet' : '';

            if (strlen(trim($arrJob['url1']))) {
                $arrJob['url'] = trim($arrJob['url1']);
            } else {
                if (strlen(trim($arrJob['url2']))) {
                    $arrJob['url'] = trim($arrJob['url2']);
                }
            }
            // Brochüre
            if ($arrJob['clinicPDF'] != null) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['clinicPDF']);
                if ($objFile != null) {
                    $arrJob['brochure'] = '/' . $objFile->path;
                }
                unset($arrJob['clinicPDF']);
            }

            //Kontaktperson: wenn nicht im Job gesetzt, dann aus der Klinik kopieren
            $arrFieldKeys = array(
                'contactperson_salutation',
                'contactperson_position',
                'contactperson_title',
                'contactperson_firstname',
                'contactperson_lastname',
                'contactperson_phone',
                'contactperson_email',
            );
            // if (!strlen(trim($arrJob['contactperson_firstname']))) {
            if ($arrJob['has_contactperson'] != 1) {
                foreach ($arrFieldKeys as $strKey) {
                    $arrJob[$strKey] = $arrJob['clinic_' . $strKey];
                }
            }
            foreach ($arrFieldKeys as $strKey) {
                unset($arrJob['clinic_' . $strKey]);
            }

            $arrJob['mailto'] = $arrJob['contactperson_email'] . '?subject=' . rawurlencode($arrJob['jobTitle'] . ' - ' . $arrJob['subjectTitle'] . ' in ' . $arrJob['city']);

            // Auszeichungen
            if ($arrJob['addAwardImage1'] == '1' && $arrJob['awardImage1'] != null) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['awardImage1']);
                if ($objFile) {
                    $arrJob['awardImage1'] = '/' . $objFile->path;
                } else {
                    unset($arrJob['awardImage1']);
                    unset($arrJob['awardImage1Alt']);
                }
            } else {
                unset($arrJob['awardImage1']);
                unset($arrJob['awardImage1Alt']);
            }
            if ($arrJob['addAwardImage2'] == '1' && $arrJob['awardImage2'] != null) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['awardImage2']);
                if ($objFile) {
                    $arrJob['awardImage2'] = '/' . $objFile->path;
                } else {
                    unset($arrJob['awardImage2']);
                    unset($arrJob['awardImage2Alt']);
                }
            } else {
                unset($arrJob['awardImage2']);
                unset($arrJob['awardImage2Alt']);
            }

            $arrJobs[$intId] = $arrJob;
            // mapping aufbauen
            $arrFixedJobs[$arrJob['id']] = $intId;
        }

        $objDetailTemplate = new FrontendTemplate('mod_jobdetails');
        $objDetailTemplate->job = $arrJobs[0];

        // Detail-Seiten-Link bauen

        if (isset($GLOBALS['TL_CONFIG']['detailsPage']) && (int) $GLOBALS['TL_CONFIG']['detailsPage']) {
            $objPage = PageModel::findByPk($GLOBALS['TL_CONFIG']['detailsPage']);
            if ($objPage) {
                $strDetailUrl = str_replace('.html', '', $this->generateFrontendUrl($objPage->row())) . '/';
                $this->Template->detailUrl = $strDetailUrl;
            }
        }

        // arrShortJobTypes soll sortiert werden: Assi, Arzt, Facharzt, Leitender OA, Chef
        // => 7, 1, 3, 4, 2
        $sortJobArr = function ($arr) {
            // db-indizes nutzen
            $arrTmp = [];
            foreach ($arr as $arrJob) {
                $arrTmp[$arrJob['id']] = $arrJob; //['title'];
            }
            $arrRet = [];
            foreach (array(7, 1, 3, 4, 2) as $intIndex) {
                if (isset($arrTmp[$intIndex])) {
                    $arrRet[] = $arrTmp[$intIndex];
                    unset($arrTmp[$intIndex]);
                }
            }
            // wenn noch (neue) übrig --> hinten anhängen
            if (count($arrTmp)) {
                $arrRet = array_merge($arrRet, $arrTmp);
            }
            return $arrRet;
        };
        $arrSortedShortJobTypes = $sortJobArr($arrShortJobTypes);
        $arrSortedJobTypes = $sortJobArr($arrJobTypes);

        $this->Template->short_job_types = $arrSortedShortJobTypes; //$arrShortJobTypes;
        $this->Template->job_types = $arrSortedJobTypes;
        $this->Template->jobs = $arrJobs;
        $this->Template->job_subjects = $arrJobFields;
        $this->Template->job_mapping = $arrFixedJobs;
        $this->Template->detailTemplate = $objDetailTemplate->parse();
        $this->Template->subject_images = $this->getSubjectImages();
    }

    /**
     * Jedes Fachgebiet hat bis zu 3 Bilder.
     *
     * Eines davon wird (zufällig ausgewählt und) auf der Detail-Seite dargestellt
     */
    protected function getSubjectImages()
    {
        $arrSubjects = [];
        $arrDbSubjects = $this->Database->prepare('SELECT id, img1, img1Alt, img2, img2Alt, img3, img3Alt FROM tl_subjects')->execute()->fetchAllAssoc();

        // schauen, ob Bilder gesetzt sind; wenn ja, dann Pfade holen
        foreach ($arrDbSubjects as $arrSubject) {
            $arrFiles = [];
            $boolFound = false;
            foreach (['img1', 'img2', 'img3'] as $strFieldName) {
                if ($strPath = $this->getPathFromFileObj($arrSubject[$strFieldName])) {
                    $boolFound = true;
                    $arrFiles[] = [
                        'path' => $strPath,
                        'alt' => $arrSubject[$strFieldName . 'Alt'],
                    ];
                }
            }
            if ($boolFound) {
                $arrSubjects[$arrSubject['id']] = $arrFiles;
            }
        }
        return $arrSubjects;
    }

    private function getPathFromFileObj($strUuid)
    {
        if (!$strUuid) {
            return false;
        }
        $objFile = \FilesModel::findOneBy('uuid', $strUuid);
        if (!$objFile) {
            return false;
        }
        return '/' . $objFile->path;
    }
}
