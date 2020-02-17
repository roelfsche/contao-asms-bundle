<?php

namespace Lumturo\ContaoAsmsBundle\Module;

use Contao\Environment;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Patchwork\Utf8;

class JobdetailsModule extends \Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_jobdetails';

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

        if (TL_MODE == 'BE') {
            $template = new \BackendTemplate('be_wildcard');

            $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['Jobdeatils'][0]) . ' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        } else {
        }


        return parent::generate();
    }

    /**
     * Generates the module.
     */
    protected function compile()
    {
        // $objJob
        $strAlias = $this->Input->get('auto_item');
        if (!$strAlias) {
            $this->redirect('/');
        }

        $strSQL = 'select 
            a.id, 
            a.jobID as jobId, 
            a.typeFulltime, a.typeParttime, a.typeLimited, a.weOffer, a.youOffer, a.applicationNotes, 
            a.stop,
            a.titleSelection as jobType,
            a.tstamp,
            a.subjectSelection as jobSubject,
            b.title as clinicTitle, 
            b.city, 
            b.city as city2, 
            b.state,
            b.logo as clinicLogo,
            b.logoAlt as clinicLogoAlt,
            b.optionalImage,
            b.optionalImageAlt,
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
            where a.alias = ? AND a.published = \'1\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?)
            LIMIT 1';
        $arrJobs = $this->Database->prepare($strSQL)->execute($strAlias, time(), time())->fetchAllAssoc();
        if (!$arrJobs || !count($arrJobs)) {
            $this->redirect('/');
        }

        $arrJob = $arrJobs[0];
        // id -> index; Vollzeit / Teilzeit
        // $arrFixedJobs = [];
        // foreach ($arrJobs as $intId => $arrJob) {
        // city aus title raus
        $arrJob['clinicTitle'] = str_replace(' ' . $arrJob['city'], '', $arrJob['clinicTitle']);
        // Logo
        if ($arrJob['clinicLogo'] != NULL) {
            $objFile = FilesModel::findOneBy('uuid', $arrJob['clinicLogo']);
            if ($objFile) {
                $arrJob['clinicLogo'] = '//' . Environment::get('host') . '/' . $objFile->path;
            } else {
                unset($arrJob['clinicLogo']);
                unset($arrJob['clinicLogoAlt']);
            }
        } else {
            unset($arrJob['clinicLogo']);
            unset($arrJob['clinicLogoAlt']);
        }

        // Kilinikbild
        if ($arrJob['optionalImage'] != NULL) {
            $objFile = FilesModel::findOneBy('uuid', $arrJob['optionalImage']);
            if ($objFile) {
                $arrJob['optionalImage'] = '//' . Environment::get('host') . '/' . $objFile->path;
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

        // $arrJobs[$intId] = $arrJob;
        // mapping aufbauen
        // $arrFixedJobs[$arrJob['id']] = $intId;
        // }

        // $objDetailTemplate = new FrontendTemplate('mod_jobdetails');
        // $objDetailTemplate->job = $arrJob;
        $objGoogleJobTemplate = new FrontendTemplate('google_job');
        $objGoogleJobTemplate->arrJob = $arrJob;

        $this->Template->job = $arrJob;
        $this->Template->google_job = $objGoogleJobTemplate;
        // $this->Template->job_types = $arrJobTypes;
        // $this->Template->jobs = $arrJobs;
        // $this->Template->job_subjects = $arrJobFields;
        // $this->Template->job_mapping = $arrFixedJobs;
        // $this->Template->detailTemplate = $objDetailTemplate->parse();
        $arrSubjectImages = $this->getSubjectImages($arrJob['jobSubject']);

        $this->Template->subject_image = $this->getSubjectImages($arrJob['jobSubject']);
    }

    /**
     * Jedes Fachgebiet hat bis zu 3 Bilder.
     * 
     * Eines davon wird (zufällig ausgewählt und) auf der Detail-Seite dargestellt
     */
    protected function getSubjectImages($id)
    {
        $arrSubjects = [];
        $arrDbSubjects = $this->Database->prepare('SELECT id, img1, img1Alt, img2, img2Alt, img3, img3Alt FROM tl_subjects WHERE id = ?')->execute($id)->fetchAllAssoc();

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
                shuffle($arrFiles);
                return $arrFiles[0];
            }
        }
        return null;
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
