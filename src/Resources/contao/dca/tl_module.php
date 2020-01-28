<?php

// Add palette to tl_module
$GLOBALS['TL_DCA']['tl_module']['palettes']['Joblist'] = '{title_legend},name,headline,type;{filter_legend:hide},detailsPage,subjects,max_results';
$GLOBALS['TL_DCA']['tl_module']['fields']['subjects'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['subjects'],
    'inputType'               => 'select',
    'options_callback' => array('Module_Helper', 'getJobFunctions'),
    'eval' => [
        'includeBlankOption' => true,
    ],
    // 'foreignKey'              => 'tl_subjects.title',
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    // 'relation'                => array('type'=>'hasOne', 'load'=>'eager')
];
$GLOBALS['TL_DCA']['tl_module']['fields']['max_results'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['max_results'],
    'inputType'               => 'text',
    'eval' => [
        'rgxp' => 'digit'
    ],
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['detailsPage'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['detailsPage'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    // 'sql'                     => "binary(16) NULL"
);

class Module_Helper extends Backend
{
    public function getJobFunctions()
    {
        $arrRetTypes = [];
        $arrTypes = $this->Database->prepare('select id, title from tl_subjects ORDER BY title;')->execute()->fetchAllAssoc();
        foreach ($arrTypes as $arrType) {
            $arrRetTypes[$arrType['id']] = $arrType['title'];
        }
        return $arrRetTypes;
    }
}
