<?php

/**
 * Table tl_survey_result
 */
$GLOBALS['TL_DCA']['tl_survey_result'] = [

    // Config
    'config' => [
        'ptable'           => 'tl_survey',
        'doNotCopyRecords' => true,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
                'qid' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id'     => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pin'    => [
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'uid'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'qid'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'result' => [
            'sql' => "text NULL",
        ],
    ],
];

