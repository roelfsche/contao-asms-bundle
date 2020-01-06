<?php

/**
 * Stammdaten
 * Copyright (c) 2014 Stefan Becker
 */

$table = 'tl_clinics';

/**
 * Table tl_jobs
 */
$GLOBALS['TL_DCA'][$table] = array(
    // Config
    'config' => array(
        'dataContainer'               => 'Table',
        'sql' => array(
            'keys' => array(
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array(
        'sorting' => array(
            'mode'                    => 2,
            'flag'                      => 1,
            'fields'                  => array('title'),
            'format'                  => '%s',
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array(
            'fields'                  => array('title'),
            'format'                  => '%s'
        ),
        'global_operations' => array(
            'all' => array(
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array(
            'edit' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array(
                'label'               => &$GLOBALS['TL_LANG'][$table]['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array(
        '__selector__'                => array('addOptionalImage', 'addAwardImage1', 'addAwardImage2'),
        'default'                     => '{lbl_general},title;{lbl_logo},logo,logoAlt,addOptionalImage;{lbl_location},department,street,houseNumber,zipCode,city,state,lat,lon;{lbl_contactperson},contactperson_position,contactperson_salutation,contactperson_title,contactperson_firstname,contactperson_lastname,contactperson_email,contactperson_phone;{lbl_additional},url1,url2,clinicPDF,clinicVideo;{lbl_social},social_facebook,social_gplus,social_twitter;{lbl_award},addAwardImage1;{lbl_equality},equality'
    ),

    // Subpalettes
    'subpalettes' => array(
        'addOptionalImage'            => 'optionalImage,optionalImageAlt',
        'addAwardImage1'              => 'awardImage1,awardImage1Alt,addAwardImage2',
        'addAwardImage2'              => 'awardImage2,awardImage2Alt'
    ),

    // Fields
    'fields' => array(
        'id' => array(
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array(
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'maxlength' => 150, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'department' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['department'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 150, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'street' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['street'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'houseNumber' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['houseNumber'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 100, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'zipCode' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['zipCode'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'digit'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'city' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['city'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'state' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['state'],
            'default'                 => 'regular',
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array($table, 'getStates'),
            'eval'                    => array('tl_class' => 'w100'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'lat' => array(
            'sorting' => true,
            'label' => &$GLOBALS['TL_LANG'][$table]['lat'],
            'exclude' => false,
            'inputType' => 'text',
            'save_callback' => array(
                array(
                    'tl_clinics',
                    'saveLat'
                )
            ),
            'eval' => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w100'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
        'lon' => array(
            'sorting' => true,
            'label' => &$GLOBALS['TL_LANG'][$table]['lon'],
            'exclude' => false,
            'inputType' => 'text',
            'save_callback' => array(
                array(
                    'tl_clinics',
                    'saveLon'
                )
            ),
            'eval' => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w100'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
        'logo' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['logo'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'logoAlt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'addOptionalImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addOptionalImage'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'optionalImage' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['optionalImage'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'optionalImageAlt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_salutation' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_salutation'],
            'default'                 => 'regular',
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array($table, 'getSalutation'),
            'eval'                    => array(),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'contactperson_position' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_position'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_title' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_firstname' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_firstname'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_lastname' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_email' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_email'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactperson_phone' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['contactperson_phone'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'extnd'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'url1' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['url1'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'url'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'url2' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['url2'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'url'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'social_facebook' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['social_facebook'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'url'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'social_gplus' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['social_gplus'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'url'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'social_twitter' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['social_twitter'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'url'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'addAwardImage1' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addAwardImage1'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'awardImage1' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['awardImage1'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'awardImage1Alt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'addAwardImage2' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['addAwardImage2'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange' => true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'awardImage2' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['awardImage2'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'awardImage2Alt' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength' => 255, 'rgxp' => 'alnum'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'equality' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['equality'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory' => false, 'rte' => 'tinyMCE', 'maxlength' => 500),
            'sql'                     => "mediumtext NULL"
        ),
        'clinicPDF' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['clinicPDF'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
        'clinicVideo' => array(
            'label'                   => &$GLOBALS['TL_LANG'][$table]['clinicVideo'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'                     => "binary(16) NULL"
        ),
    )
);


/**
 * Class tl_clinics
 */
class tl_clinics extends Backend
{
    /**
     * Returns all salutations
     * @param \DataContainer
     * @return string
     */
    public function getSalutation(DataContainer $dc)
    {
        $arrOptions = array();

        $arrOptions[] = "Herr";
        $arrOptions[] = "Frau";

        return $arrOptions;
    }

    /**
     * Returns all states
     * @param \DataContainer
     * @return string
     */
    public function getStates(DataContainer $dc)
    {
        $arrOptions = array();

        $arrOptions[] = "Baden-Württemberg";
        $arrOptions[] = "Bayern";
        $arrOptions[] = "Berlin";
        $arrOptions[] = "Brandenburg";
        $arrOptions[] = "Bremen";
        $arrOptions[] = "Hamburg";
        $arrOptions[] = "Hessen";
        $arrOptions[] = "Mecklenburg-Vorpommern";
        $arrOptions[] = "Niedersachsen";
        $arrOptions[] = "Nordrhein-Westfalen";
        $arrOptions[] = "Rheinland-Pfalz";
        $arrOptions[] = "Saarland";
        $arrOptions[] = "Sachsen";
        $arrOptions[] = "Sachsen-Anhalt";
        $arrOptions[] = "Schleswig-Holstein";
        $arrOptions[] = "Thüringen";

        return $arrOptions;
    }
    public function saveLon($value, $objDca)
    {
        return $this->savePos('lon', $value, $objDca);
    }

    public function saveLat($value, $objDca)
    {
        return $this->savePos('lat', $value, $objDca);
    }

    private function savePos($strField, $value, $objDca)
    {
        if (floatval($value)) {
            return $value;
        }
        if ($this->objNominatimResponse) {
            return $this->objNominatimResponse->{$strField};
        }

        $arrQueryValues = [
            trim($this->Input->post('postal') . ' ' . $this->Input->post('city')),
            str_replace(',', '', $this->Input->post('street'))
        ];
        $headers = [];
        // $headers = array('Accept' => 'application/json');
        $query = array(
            'q' => urldecode(implode(',', $arrQueryValues)),
            'format' => 'json'
        );

        $response = Unirest\Request::get('https://nominatim.openstreetmap.org/search', $headers, $query);
        if ($response->code == 200) {
            if (isset($response->body[0])) {
                $this->objNominatimResponse = $response->body[0];
            }
        }
        if ($this->objNominatimResponse && isset($this->objNominatimResponse->{$strField})) {
            return $this->objNominatimResponse->{$strField};
        }
        return $value;
    }
}
