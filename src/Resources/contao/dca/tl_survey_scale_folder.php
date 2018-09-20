<?php

/**
 * Table tl_survey
 */
$GLOBALS['TL_DCA']['tl_survey_scale_folder'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'ctable'           => ['tl_survey_scale'],
        'switchToEdit'     => true,
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
            'mode'        => 2,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
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
                'label' => &$GLOBALS['TL_LANG']['tl_survey_scale_folder']['edit'],
                'href'  => 'table=tl_survey_scale',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_scale_folder']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_survey_scale_folder']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_scale_folder']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => 'title,description',
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
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_scale_folder']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'description' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_scale_folder']['description'],
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => true, 'style' => 'height:80px;'],
            'sql'       => "text NULL",
        ],
    ],
];


/**
 * Class tl_survey_scale_folder
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_survey_scale_folder extends Backend
{

}

