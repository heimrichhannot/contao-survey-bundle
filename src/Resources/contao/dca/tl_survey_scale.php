<?php

/**
 * Table tl_survey_scale
 */
$GLOBALS['TL_DCA']['tl_survey_scale'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_survey_scale_folder',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    // List
    'list'     => [
        'sorting'           => [
            'mode'                  => 4,
            'filter'                => true,
            'fields'                => ['title'],
            'panelLayout'           => 'search,filter,limit',
            'flag'                  => 11,
            'headerFields'          => ['title', 'tstamp', 'description'],
            'child_record_callback' => ['tl_survey_scale', 'compilePreview'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_scale']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_scale']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_survey_scale']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_survey_scale']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_scale']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,description,language;{scale_legend},scale',
    ],

    // Fields
    'fields'   => [
        'id'          => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_scale']['title'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'pid'         => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'description' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_scale']['description'],
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => true, 'style' => 'height:80px;'],
            'sql'       => "text NULL",
        ],
        'scale'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_scale']['scale'],
            'exclude'   => true,
            'inputType' => 'textwizard',
            'eval'      => ['allowHtml' => true, 'mandatory' => true],
            'sql'       => "blob NULL",
        ],
        'language'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_scale']['language'],
            'default'   => $GLOBALS['TL_LANGUAGE'],
            'filter'    => true,
            'inputType' => 'select',
            'options'   => $this->getLanguages(),
            'eval'      => ['includeBlankOption' => true],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_survey_scale
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_survey_scale extends Backend
{
    /**
     * Compile format definitions and return them as string
     *
     * @param array
     * @param boolean
     *
     * @return string
     */
    public function compilePreview($row, $blnWriteToFile = false)
    {
        $result  = '<p><strong>' . $row['title'] . '</strong></p>';
        $result  .= "<ol>";
        $answers = deserialize($row['scale'], true);
        foreach ($answers as $answer) {
            $result .= '<li>' . specialchars($answer) . '</li>';
        }
        $result .= "</ol>";

        return $result;
    }
}

