<?php

namespace vacancies;

use Contao\Environment;
use Contao\Frontend;
use Contao\PageModel;

/**
 * (c) 2020 rolf.staege@lumturo.net 
 */
class FrontendHooks extends Frontend
{
    public function getSearchablePages($arrPages, $intRoot = 0, $blnIsSitemap = false, $strLang)
    {
        if (!isset($GLOBALS['TL_CONFIG']['detailsPage'])) {
            return $arrPages;
        }

        // $objPage = PageModel::findByPk($GLOBALS['TL_CONFIG']['detailsPage']);
        $objPage = PageModel::findWithDetails($GLOBALS['TL_CONFIG']['detailsPage']);
        if (!$objPage) {
            return $arrPages;
        }

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
        $arrJobs = $this->Database->prepare($strSQL)->execute(time(), time())->fetchAllAssoc();
        // $objResult = $this->Database->prepare($strSQL)->execute(time(), time());

        $strDetailUrl = $objPage->getAbsoluteUrl(\Config::get('useAutoItem') ? '/%s' : '/items/%s');
        // $strDetailUrl = Environment::get('host') .  '/' . str_replace('.html', '', $this->generateFrontendUrl($objPage->row())) . '/';
        foreach($arrJobs as $arrJob) {
        // foreach($arrJobs as $arrJob) {
            $strUrl = sprintf(preg_replace('/%(?!s)/', '%%', $strDetailUrl), ($arrJob['jobAlias'] ?: $arrJob['id']));
            // $strUrl = $strDetailUrl . $arrJob['jobAlias'] . '.html';
            $arrPages[] = $strUrl;
        }

        return $arrPages;
    }
}
