<?php

namespace Lumturo\ContaoAsmsBundle\Module;

use Contao\FilesModel;
use Contao\FrontendTemplate;
use Patchwork\Utf8;

class JobmapModule extends \Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_jobmap';

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

            $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['Jobmap'][0]) . ' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        } else {
            // if ($this->intSubjectFilterValue == 0) {
            // $GLOBALS['TL_JAVASCRIPT'][] = 'https://code.jquery.com/jquery-3.4.1.min.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaoasms/js/leaflet.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaoasms/js/map.js';
            $GLOBALS['TL_CSS'][] = 'bundles/contaoasms/css/leaflet.css';
            // }
        }


        return parent::generate();
    }

    /**
     * Generates the module.
     */
    protected function compile()
    {
            $strSQL = 'select 
            a.id, 
            b.title as clinicTitle, 
            b.city, 
            b.optionalImage,
            b.optionalImageAlt,
            b.zipCode, 
            b.lat, 
            b.lon, 
            b.department, 
            b.street, 
            b.houseNumber, 
            b.zipCode, 
            b.url1, 
            b.url2, 
            b.clinicPDF, 
            c.title as jobTitle, 
            d.title as subjectTitle, d.title as subjectTitle2,
            d.id as subjectId 
            from tl_jobs a 
            join tl_clinics b on (a.clinic = b.id) 
            join tl_jobtypes c on (a.titleSelection = c.id) 
            join tl_subjects d on (a.subjectSelection = d.id) 
            where a.published = \'1\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?)
            AND b.lat!=\'\' AND b.lon!=\'\'';
            $arrJobs = $this->Database->prepare($strSQL)->execute(time(), time())->fetchAllAssoc();

        // id -> index; Vollzeit / Teilzeit
        $arrFixedJobs = [];
        foreach ($arrJobs as $intId => $arrJob) {
            // Logo
            if ($arrJob['optionalImage'] != NULL) {
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

            // $arrJob['typeFulltime'] = ($arrJob['typeFulltime'] == 1) ? 'Vollzeit' : '';
            // $arrJob['typeParttime'] = ($arrJob['typeParttime'] == 1) ? 'Teilzeit' : '';
            // $arrJob['typeLimited'] = ($arrJob['typeLimited'] == 1) ? 'Befristet' : '';
            // $arrJob['mailto'] = $arrJob['contactperson_email'] . '?subject=' . rawurlencode($arrJob['jobTitle'] . ' - ' . $arrJob['subjectTitle']);

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
            // if ($arrJob['awardImage1'] != NULL) {
            //     $objFile = FilesModel::findOneBy('uuid', $arrJob['awardImage1']);
            //     if ($objFile) {
            //         $arrJob['awardImage1'] = '/' . $objFile->path;
            //     } else {
            //         unset($arrJob['awardImage1']);
            //         unset($arrJob['awardImage1Alt']);
            //     }
            // } else {
            //     unset($arrJob['awardImage1']);
            //     unset($arrJob['awardImage1Alt']);
            // }
            // if ($arrJob['awardImage2'] != NULL) {
            //     $objFile = FilesModel::findOneBy('uuid', $arrJob['awardImage2']);
            //     if ($objFile) {
            //         $arrJob['awardImage2'] = '/' . $objFile->path;
            //     } else {
            //         unset($arrJob['awardImage2']);
            //         unset($arrJob['awardImage2Alt']);
            //     }
            // } else {
            //     unset($arrJob['awardImage2']);
            //     unset($arrJob['awardImage2Alt']);
            // }

            $arrJobs[$intId] = $arrJob;
            // mapping aufbauen
            $arrFixedJobs[$arrJob['id']] = $intId;
        }

        $objDetailTemplate = new FrontendTemplate('mod_jobdetails');
        $objDetailTemplate->job = $arrJobs[0];

        // $this->Template->short_job_types = $arrShortJobTypes;
        // $this->Template->job_types = $arrJobTypes;
        $this->Template->jobs = $arrJobs;
        // $this->Template->job_subjects = $arrJobFields;
        // $this->Template->job_mapping = $arrFixedJobs;
        // $this->Template->detailTemplate = $objDetailTemplate->parse();
        // $this->Template->subject_images = $this->getSubjectImages();
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
