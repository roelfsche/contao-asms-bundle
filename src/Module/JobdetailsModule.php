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
            a.typeFulltime, a.typeParttime, a.typeLimited, a.weOffer, a.youOffer, a.applicationNotes, a.aboutUs,
            a.stop,
            a.titleSelection as jobType,
            a.tstamp,
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
            b.state,
            b.logo as clinicLogo,
            b.logoAlt as clinicLogoAlt,
            b.optionalImage,
            b.optionalImageAlt,
            b.zipCode,
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
            where a.alias = ? AND a.published = \'1\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?)
            LIMIT 1';
        $arrJobs = $this->Database->prepare($strSQL)->execute($strAlias, time(), time())->fetchAllAssoc();
        if (!$arrJobs || !count($arrJobs)) {
            $this->redirect('/');
        }

        $arrJob = $arrJobs[0];

        // Fach ist nun als json-array am Objket
        // mache daraus erstmal ein Array und filtere den Job raus, wenn nicht zugehörig
        $arrSubjectIds = array(); // merke mir auch alle zugeordneten Fächer
        $intSubjectId = (int) $arrJob['jobSubject'];
        if ($intSubjectId) {
            $arrSubjects = array($intSubjectId);
            $arrSubjectIds[$intSubjectId] = $intSubjectId;
        } else {
            $arrSubjects = \Contao\StringUtil::deserialize($arrJob['jobSubject']);
        }
        $arrJob['subjects'] = $arrSubjects;
        // // und merke mir die Id's über alle (für filter)
        // foreach ($arrSubjects as $intSubjectId) {
        //     $arrSubjectIds[$intSubjectId] = $intSubjectId;
        // }

        $arrJobFields = $this->Database->prepare('select a.id, a.title from tl_subjects a WHERE a.id in (' . implode(', ', $arrSubjects) . ') ORDER BY a.title;')->execute()->fetchAllAssoc();
        $arrSubjectNames = array();
        foreach ($arrJobFields as $arrSubjectRow) {
            $arrSubjectNames[$arrSubjectRow['id']] = $arrSubjectRow['title'];
        }
        $arrJobSubjectNames = array();
        foreach ($arrJob['subjects'] as $intSubjectId) {
            $arrJobSubjectNames[$intSubjectId] = $arrSubjectNames[$intSubjectId];
        }
        $arrJob['subjectNames'] = $arrJobSubjectNames;
        $arrJob['subjectTitle'] = implode(', ', $arrJobSubjectNames);
        // SubjectImage
        // neue Jobs bringen das mit
        if ($arrJob['subjectImage'] != null) {
            $objFile = FilesModel::findOneBy('uuid', $arrJob['subjectImage']);
            if ($objFile) {
                $this->Template->subject_image = [
                    'path' => '/' . $objFile->path,
                    'alt' => $arrJob['subjectImageAlt'],
                ];
            }
        } else {
            $this->Template->subject_image = $this->getSubjectImages($arrJob['jobSubject']);
        }
        unset($arrJob['subjectImage']);
        unset($arrJob['subjectImageAlt']);

        global $jobImage;
        $jobImage = '//' . \Environment::get('httpHost') . $this->Template->subject_image['path'];

        // Logo
        if ($arrJob['clinicLogo'] != null) {
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
        if ($arrJob['optionalImage'] != null) {
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

        if (strlen(trim($arrJob['url1']))) {
            $arrJob['url'] = trim($arrJob['url1']);
        } else {
            if (strlen(trim($arrJob['url2']))) {
                $arrJob['url'] = trim($arrJob['url2']);
            }
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
        if ($arrJob['has_contactperson']!=1) {
        // if (!strlen(trim($arrJob['contactperson_firstname']))) {
            foreach ($arrFieldKeys as $strKey) {
                $arrJob[$strKey] = $arrJob['clinic_' . $strKey];
            }
        }
        foreach ($arrFieldKeys as $strKey) {
            unset($arrJob['clinic_' . $strKey]);
        }

        $arrJob['mailto'] = $arrJob['contactperson_email'] . '?subject=' . rawurlencode($arrJob['jobTitle'] . ' - ' . $arrJob['subjectTitle'] . ' in ' . $arrJob['city']);
        // Brochüre
        if ($arrJob['clinicPDF'] != null) {
            $objFile = FilesModel::findOneBy('uuid', $arrJob['clinicPDF']);
            if ($objFile != null) {
                $arrJob['brochure'] = '/' . $objFile->path;
            }
            unset($arrJob['clinicPDF']);
        }

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

        $objGoogleJobTemplate = new FrontendTemplate('google_job');
        $objGoogleJobTemplate->arrJob = $arrJob;

        $this->Template->job = $arrJob;
        $this->Template->google_job = $objGoogleJobTemplate;

        // title setzen
        // nicht schön aber hat Marco vorgeschlagen und funktioniert
        global $job_detail_title;
        $job_detail_title = 'Stellenangebot - ' . $arrJob['jobTitle'] . ' - ' . $arrJob['subjectTitle'] . ' in ' . $arrJob['city'] . ' - Arzt sein. Mensch sein.';

        global $job_detail_decription;
        $job_detail_decription = 'Stellenangebot ✔ ' . $arrJob['jobTitle'] . ' ✔ ' . $arrJob['subjectTitle'] . ' in ' . $arrJob['city'] . '. ➤ Traumjob gefällig?';

        global $job_share_image;
        $job_share_image = $this->subject_image['path'];
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
                // shuffle($arrFiles);
                return $arrFiles[0];
            }
        }
        return null;
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
