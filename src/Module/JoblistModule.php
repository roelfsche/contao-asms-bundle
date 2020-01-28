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
     * Displays a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        $this->intSubjectFilterValue = (int) $this->subjects;

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
            $GLOBALS['TL_JAVASCRIPT'][] = 'https://code.jquery.com/jquery-3.4.1.min.js';
            $GLOBALS['TL_JAVASCRIPT'][] = '//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js';
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
        if (!$this->intSubjectFilterValue) {
            $this->Template->show_filter = TRUE;
            // selektiere alle Job-Typen für Filter
            $arrShortJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE a.showInLinkList = 1 AND b.published=\'1\';')->execute()->fetchAllAssoc();
            $arrJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE b.published=\'1\';')->execute()->fetchAllAssoc();
            $arrJobFields = $this->Database->prepare('select distinct a.id, a.title from tl_subjects a join tl_jobs b on (a.id = b.subjectSelection) WHERE b.published=\'1\' ORDER BY a.title;')->execute()->fetchAllAssoc();
            $strSQL = 'select 
            a.id, 
            a.jobID as jobId, 
            a.alias as jobAlias,
            a.typeFulltime, a.typeParttime, a.typeLimited, a.weOffer, a.youOffer, a.applicationNotes, 
            a.titleSelection as jobType,
            a.subjectSelection as jobSubject,
            b.title as clinicTitle, 
            b.city, 
            b.city as city2, 
            b.logo as clinicLogo,
            b.logoAlt as clinicLogoAlt,
            b.zipCode, 
            b.lat, 
            b.lon, 
            b.contactperson_salutation, 
            b.contactperson_title , 
            b.contactperson_firstname, 
            b.contactperson_lastname, 
            b.contactperson_phone, 
            b.contactperson_email, 
            b.department, 
            b.street, 
            b.houseNumber, 
            b.zipCode, 
            b.url1, 
            b.url2, 
            b.clinicPDF, 
            b.awardImage1,
            b.awardImage1Alt,
            b.awardImage2,
            b.awardImage2Alt,
            b.equality, 
            c.title as jobTitle, c.title as jobTitle2, d.title as subjectTitle, d.title as subjectTitle2,
            d.id as subjectId 
            from tl_jobs a 
            join tl_clinics b on (a.clinic = b.id) 
            join tl_jobtypes c on (a.titleSelection = c.id) 
            join tl_subjects d on (a.subjectSelection = d.id) 
            where a.published = \'1\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?)';
            if (($strCity = $this->Input->get('city')) != '') {
                $strSQL .= " AND b.city = ?";
                $objResult = $this->Database->prepare($strSQL)->execute(time(), time(), $strCity);
                $arrJobs = $objResult->fetchAllAssoc();
            } else {
                $arrJobs = $this->Database->prepare($strSQL)->execute(time(), time())->fetchAllAssoc();
            }
        } else {
            // selektiere alle Job-Typen für Filter
            $arrShortJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE a.showInLinkList = 1 AND b.published=\'1\' AND b.subjectSelection=?;')->execute($this->intSubjectFilterValue)->fetchAllAssoc();
            $arrJobTypes = $this->Database->prepare('select distinct a.id, a.title from tl_jobtypes a join tl_jobs b on (a.id = b.titleSelection) WHERE b.published=\'1\' AND b.subjectSelection=?;')->execute($this->intSubjectFilterValue)->fetchAllAssoc();
            $arrJobFields = $this->Database->prepare('select distinct a.id, a.title from tl_subjects a join tl_jobs b on (a.id = b.subjectSelection) WHERE b.published=\'1\' AND b.subjectSelection=? ORDER BY a.title;')->execute($this->intSubjectFilterValue)->fetchAllAssoc();
            $strSQL = 'select 
                        a.id, 
                        a.jobID as jobId, 
                        a.alias as jobAlias,
                        a.typeFulltime, a.typeParttime, a.typeLimited, a.weOffer, a.youOffer, a.applicationNotes, 
                        a.titleSelection as jobType,
                        a.subjectSelection as jobSubject,
                        b.title as clinicTitle, 
                        b.city, 
                        b.city as city2, 
                        b.logo as clinicLogo,
                        b.logoAlt as clinicLogoAlt,
                        b.zipCode, 
                        b.lat, 
                        b.lon, 
                        b.contactperson_salutation, 
                        b.contactperson_title , 
                        b.contactperson_firstname, 
                        b.contactperson_lastname, 
                        b.contactperson_phone, 
                        b.contactperson_email, 
                        b.department, 
                        b.street, 
                        b.houseNumber, 
                        b.zipCode, 
                        b.url1, 
                        b.url2, 
                        b.clinicPDF, 
                        b.awardImage1,
                        b.awardImage1Alt,
                        b.awardImage2,
                        b.awardImage2Alt,
                        b.equality, 
                        c.title as jobTitle, c.title as jobTitle2, d.title as subjectTitle, d.title as subjectTitle2,
                        d.id as subjectId
                        from tl_jobs a 
                        join tl_clinics b on (a.clinic = b.id) 
                        join tl_jobtypes c on (a.titleSelection = c.id) 
                        join tl_subjects d on (a.subjectSelection = d.id) 
                        where a.published = \'1\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?) AND a.subjectSelection=?';
            if (($strCity = $this->Input->get('city')) != '') {
                $strSQL .= ' AND b.city = ?';
                $objResult = $this->Database->prepare($strSQL)->execute(time(), time(), $this->intSubjectFilterValue, $strCity);
                $arrJobs = $objResult->fetchAllAssoc();
            } else {
                $arrJobs = $this->Database->prepare($strSQL)->execute(time(), time(), $this->intSubjectFilterValue)->fetchAllAssoc();
            }
            $this->Template->show_filter = FALSE;
        }

        // wenn in der Modul-Config eine max_Anzahl hinterlegt, dann werden nur die
        // - aber zufällig - ausgegeben
        if ($this->max_results) {
            shuffle($arrJobs);
            $arrJobs = array_slice($arrJobs, 0, $this->max_results);
        }

        // id -> index; Vollzeit / Teilzeit
        $arrFixedJobs = [];
        foreach ($arrJobs as $intId => $arrJob) {
            // Logo
            if ($arrJob['clinicLogo'] != NULL) {
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

            $arrJob['typeFulltime'] = ($arrJob['typeFulltime'] == 1) ? 'Vollzeit' : '';
            $arrJob['typeParttime'] = ($arrJob['typeParttime'] == 1) ? 'Teilzeit' : '';
            $arrJob['typeLimited'] = ($arrJob['typeLimited'] == 1) ? 'Befristet' : '';
            $arrJob['mailto'] = $arrJob['contactperson_email'] . '?subject=' . rawurlencode($arrJob['jobTitle'] . ' - ' . $arrJob['subjectTitle']);

            if (strlen(trim($arrJob['url1']))) {
                $arrJob['url'] = trim($arrJob['url1']);
            } else {
                if (strlen(trim($arrJob['url2']))) {
                    $arrJob['url'] = trim($arrJob['url2']);
                }
            }
            // Brochüre
            if ($arrJob['clinicPDF'] != NULL) {
                $objFile = FilesModel::findOneBy('uuid', $arrJob['clinicPDF']);
                if ($objFile != NULL) {
                    $arrJob['brochure'] = '/' . $objFile->path;
                }
                unset($arrJob['clinicPDF']);
            }

            // Auszeichungen
            if ($arrJob['awardImage1'] != NULL) {
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
            if ($arrJob['awardImage2'] != NULL) {
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

        if ($this->detailsPage) {
            $objPage  = PageModel::findByPk($this->detailsPage);
            if ($objPage) {
                $strDetailUrl = str_replace('.html', '', $this->generateFrontendUrl($objPage->row())) . '/';
                $this->Template->detailUrl = $strDetailUrl;
            }
        }

        $this->Template->short_job_types = $arrShortJobTypes;
        $this->Template->job_types = $arrJobTypes;
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
            $boolFound = FALSE;
            foreach (['img1', 'img2', 'img3'] as $strFieldName) {
                if ($strPath = $this->getPathFromFileObj($arrSubject[$strFieldName])) {
                    $boolFound = TRUE;
                    $arrFiles[] = [
                        'path' => $strPath,
                        'alt' => $arrSubject[$strFieldName . 'Alt']
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
            return FALSE;
        }
        $objFile =  \FilesModel::findOneBy('uuid', $strUuid);
        if (!$objFile) {
            return FALSE;
        }
        return '/' . $objFile->path;
    }
}
