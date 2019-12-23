<?php

namespace Lumturo\ContaoAsmsBundle\Module;

use Patchwork\Utf8;

class JoblistModule extends \Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_joblist';

    /**
     * Displays a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $template = new \BackendTemplate('be_wildcard');

            $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['Joblist'][0]) . ' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        } else {
            $GLOBALS['TL_JAVASCRIPT'][] = 'https://code.jquery.com/jquery-3.4.1.min.js';
            $GLOBALS['TL_JAVASCRIPT'][] = '//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaoasms/js/joblist.js';
        }

        return parent::generate();
    }

    /**
     * Generates the module.
     */
    protected function compile()
    {
        $arrJobs = $this->Database->prepare('select a.id, a.typeFulltime, a.typeParttime, a.typeLimited, b.title as clinicTitle, b.city, b.city as city2, b.zipCode ,c.title as jobTitle, c.title as jobTitle2, d.title as subjectTitle, d.title as subjectTitle2 from tl_jobs a join tl_clinics b on (a.clinic = b.id) join tl_jobtypes c on (a.titleSelection = c.id) join tl_subjects d on (a.subjectSelection = d.id) where a.published = \'\' AND (a.start = \'\' or a.start < ?) AND (a.stop = \'\' or a.stop > ?)')->execute(time(), time())->fetchAllAssoc();

        // id -> index; Vollzeit / Teilzeit
        $arrFixedJobs = [];
        foreach($arrJobs as $arrJob) {
            $arrFixedJobs[$arrJob['id']] = $arrJob;
        }

        $this->Template->jobs = $arrJobs;
    }
}
