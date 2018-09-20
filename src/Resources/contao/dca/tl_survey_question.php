<?php

/**
 * Table tl_survey_question
 */
$GLOBALS['TL_DCA']['tl_survey_question'] = [

    // Config
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_survey_page',
        'ctable'            => [],
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['tl_survey_question', 'setCompleteStatus'],
        ],
        'sql'               => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    // List
    'list'        => [
        'sorting'           => [
            'mode'                  => 4,
            'filter'                => true,
            'fields'                => ['sorting'],
            'panelLayout'           => 'search,filter,limit',
            'headerFields'          => ['title', 'tstamp', 'description'],
            'child_record_callback' => ['HeimrichHannot\SurveyBundle\Question\SurveyQuestionPreview', 'compilePreview'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_survey_question']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_question']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.gif',
            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_survey_question']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_survey_question']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_survey_question']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__'                      => ['questiontype', 'openended_subtype', 'multiplechoice_subtype', 'matrix_subtype', 'addother', 'addneutralcolumn', 'addbipolar'],
        'default'                           => '{title_legend},title,questiontype',
        'openended'                         => '{title_legend},title,author,questiontype,openended_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},openended_textbefore,openended_textafter,openended_textinside,openended_width,openended_maxlen;{expert_legend:hide},cssClass',
        'multiplechoice'                    => '{title_legend},title,author,questiontype,multiplechoice_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},choices;{expert_legend:hide},cssClass',
        'openendedoe_multiline'             => '{title_legend},title,author,questiontype,openended_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},openended_textbefore,openended_textafter,openended_textinside,openended_rows,openended_cols,openended_maxlen;{expert_legend:hide},cssClass',
        'openendedoe_integer'               => '{title_legend},title,author,questiontype,openended_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},openended_textbefore,openended_textafter,openended_textinside,lower_bound,upper_bound;{expert_legend:hide},cssClass',
        'openendedoe_float'                 => '{title_legend},title,author,questiontype,openended_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},openended_textbefore,openended_textafter,openended_textinside,lower_bound,upper_bound;{expert_legend:hide},cssClass',
        'openendedoe_date'                  => '{title_legend},title,author,questiontype,openended_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},openended_textbefore,openended_textafter,openended_textinside,lower_bound_date,upper_bound_date;{expert_legend:hide},cssClass',
        'openendedoe_time'                  => '{title_legend},title,author,questiontype,openended_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},openended_textbefore,openended_textafter,openended_textinside,lower_bound_time,upper_bound_time;{expert_legend:hide},cssClass',
        'multiplechoicemc_singleresponse'   => '{title_legend},title,author,questiontype,multiplechoice_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},choices,addother,mc_style;{expert_legend:hide},cssClass',
        'multiplechoicemc_dichotomous'      => '{title_legend},title,author,questiontype,multiplechoice_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},mc_style;{expert_legend:hide},cssClass',
        'multiplechoicemc_multipleresponse' => '{title_legend},title,author,questiontype,multiplechoice_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},choices,addother,mc_style;{expert_legend:hide},cssClass',
        'matrixmatrix_singleresponse'       => '{title_legend},title,author,questiontype,matrix_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{rows_legend},matrixrows;{columns_legend},matrixcolumns,addneutralcolumn;{bipolar_legend},addbipolar;{expert_legend:hide},cssClass',
        'matrixmatrix_multipleresponse'     => '{title_legend},title,author,questiontype,matrix_subtype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{rows_legend},matrixrows;{columns_legend},matrixcolumns,addneutralcolumn;{bipolar_legend},addbipolar;{expert_legend:hide},cssClass',
        'constantsum'                       => '{title_legend},title,author,questiontype,description,hidetitle,help,language;{question_legend},question;{obligatory_legend},obligatory;{specific_legend},sumchoices,inputfirst;{sum_legend},sumoption,sum;{expert_legend:hide},cssClass',
    ],

    // Subpalettes
    'subpalettes' => [
        'addother'         => 'othertitle',
        'addneutralcolumn' => 'neutralcolumn',
        'addbipolar'       => 'adjective1,adjective2,bipolarposition',
    ],

    // Fields
    'fields'      => [
        'id'                     => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'                 => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'                    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'                => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'questiontype'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_survey_question']['questiontype'],
            'default'          => 'openended',
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['tl_survey_question', 'getQuestiontypes'],
            'eval'             => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'              => "varchar(20) NOT NULL default ''",
        ],
        'title'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['title'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'insertTag' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'description'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['description'],
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => true, 'style' => 'height:80px;', 'tl_class' => 'clr', 'decodeEntities' => true],
            'sql'       => "text NULL",
        ],
        'author'                 => [
            'label'      => &$GLOBALS['TL_LANG']['tl_survey_question']['author'],
            'default'    => BackendUser::getInstance()->id,
            'filter'     => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_user.name',
            'eval'       => ['tl_class' => 'w50', 'decodeEntities' => true],
            'sql'        => "smallint(5) unsigned NOT NULL default '0'",
        ],
        'language'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['language'],
            'default'   => $GLOBALS['TL_LANGUAGE'],
            'inputType' => 'select',
            'options'   => $this->getLanguages(),
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'question'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['question'],
            'default'   => '',
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['mandatory' => false, 'allowHtml' => true, 'style' => 'height:80px;', 'rte' => 'tinyMCE', 'decodeEntities' => true],
            'sql'       => "text NOT NULL",
        ],
        'introduction'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['introduction'],
            'default'   => '',
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => true, 'style' => 'height:80px;', 'rte' => 'tinyMCE', 'decodeEntities' => true],
            'sql'       => "text NOT NULL",
        ],
        'obligatory'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['obligatory'],
            'filter'    => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'complete'               => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'original'               => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'help'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['help'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'hidetitle'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['hidetitle'],
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'lower_bound'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['lower_bound'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'style' => 'width: 5em;', 'rgxp' => 'digit', 'tl_class' => 'clr w50', 'decodeEntities' => true],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'upper_bound'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['upper_bound'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'style' => 'width: 5em;', 'rgxp' => 'digit', 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'lower_bound_date'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['lower_bound'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'rgxp' => 'date', 'datepicker' => $this->getDatePickerString(), 'tl_class' => 'clr w50 wizard'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'upper_bound_date'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['upper_bound'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'rgxp' => 'date', 'datepicker' => $this->getDatePickerString(), 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'lower_bound_time'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['lower_bound'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'rgxp' => 'time', 'tl_class' => 'clr w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'upper_bound_time'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['upper_bound'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'rgxp' => 'time', 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'openended_subtype'      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_subtype'],
            'default'          => 'oe_singleline',
            'inputType'        => 'select',
            'options_callback' => ['tl_survey_question', 'getOpenEndedSubtypes'],
            'eval'             => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'openended_textbefore'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_textbefore'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 150, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(150) NOT NULL default ''",
        ],
        'openended_textafter'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_textafter'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 150, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(150) NOT NULL default ''",
        ],
        'openended_rows'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_rows'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 3, 'rgxp' => 'digit', 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "smallint(5) unsigned NOT NULL default '5'",
        ],
        'openended_cols'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_cols'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 3, 'rgxp' => 'digit', 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "smallint(5) unsigned NOT NULL default '40'",
        ],
        'openended_width'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_width'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 3, 'style' => 'width: 5em;', 'rgxp' => 'digit', 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(4) NOT NULL default ''",
        ],
        'openended_maxlen'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_maxlen'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 5, 'style' => 'width: 5em;', 'rgxp' => 'digit', 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(5) NOT NULL default ''",
        ],
        'openended_textinside'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['openended_textinside'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 150, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(150) NOT NULL default ''",
        ],
        'multiplechoice_subtype' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_survey_question']['multiplechoice_subtype'],
            'default'          => 'mc_singleresponse',
            'inputType'        => 'select',
            'options_callback' => ['tl_survey_question', 'getMultipleChoiceSubtypes'],
            'eval'             => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'matrix_subtype'         => [
            'label'            => &$GLOBALS['TL_LANG']['tl_survey_question']['matrix_subtype'],
            'default'          => 'matrix_singleresponse',
            'inputType'        => 'select',
            'options_callback' => ['tl_survey_question', 'getMatrixSubtypes'],
            'eval'             => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'mc_style'               => [
            'label'            => &$GLOBALS['TL_LANG']['tl_survey_question']['mc_style'],
            'default'          => 'vertical',
            'inputType'        => 'select',
            'options_callback' => ['tl_survey_question', 'getMCStyleOptions'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_survey_question']['mc_style'],
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'choices'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['choices'],
            'exclude'   => true,
            'inputType' => 'textwizard',
            'wizard'    => [['tl_survey_question', 'addScaleWizard']],
            'eval'      => [
                'allowHtml'      => true,
                'decodeEntities' => true,
                'buttonTitles'   => [
                    'new'    => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_new'],
                    'copy'   => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_copy'],
                    'delete' => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_delete'],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'matrixrows'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['matrixrows'],
            'exclude'   => true,
            'inputType' => 'textwizard',
            'eval'      => [
                'allowHtml'      => true,
                'decodeEntities' => true,
                'buttonTitles'   => [
                    'new'    => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_matrixrow_new'],
                    'copy'   => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_matrixrow_copy'],
                    'delete' => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_matrixrow_delete'],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'matrixcolumns'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['matrixcolumns'],
            'exclude'   => true,
            'inputType' => 'textwizard',
            'eval'      => [
                'allowHtml'      => true,
                'decodeEntities' => true,
                'buttonTitles'   => [
                    'new'    => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_matrixcolumn_new'],
                    'copy'   => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_matrixcolumn_copy'],
                    'delete' => $GLOBALS['TL_LANG']['tl_survey_question']['buttontitle_matrixcolumn_delete'],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'addneutralcolumn'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['addneutralcolumn'],
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'neutralcolumn'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['neutralcolumn'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'mandatory' => true, 'decodeEntities' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'addother'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['addother'],
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addbipolar'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['addbipolar'],
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'adjective1'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['adjective1'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'adjective2'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['adjective2'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'bipolarposition'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['bipolarposition'],
            'default'   => 'top',
            'inputType' => 'select',
            'options'   => ['top', 'aside'],
            'reference' => &$GLOBALS['TL_LANG']['tl_survey_question']['bipolarposition'],
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'othertitle'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['othertitle'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 150, 'decodeEntities' => true],
            'sql'       => "varchar(150) NOT NULL default ''",
        ],
        'inputfirst'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['inputfirst'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'sumoption'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['sumoption'],
            'default'   => 'exact',
            'inputType' => 'select',
            'options'   => ['exact', 'max'],
            'reference' => &$GLOBALS['TL_LANG']['tl_survey_question']['sum'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'sumchoices'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['choices'],
            'exclude'   => true,
            'inputType' => 'textwizard',
            'eval'      => ['allowHtml' => true, 'decodeEntities' => true],
            'sql'       => "blob NULL",
        ],
        'sum'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['sum'],
            'default'   => 100,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 10, 'mandatory' => true, 'rgxp' => 'digit', 'decodeEntities' => true],
            'sql'       => "double NOT NULL default '0'",
        ],
        'cssClass'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_survey_question']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_survey_question
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Helmut Schottmüller 2009
 * @author     Helmut Schottmüller <typolight@aurealis.de>
 * @package    Controller
 */
class tl_survey_question extends Backend
{
    /**
     * Add an image to each record
     *
     * @param array
     * @param string
     *
     * @return string
     */
    public function addIcon($row, $label)
    {
        return sprintf('<div class="list_icon" style="background-image:url(\'bundles/heimrichhannotcontaosurvey/img/question.png\');">%s</div>', $label);
    }

    /**
     * Return all questiontypes as an array
     *
     * @return array
     */
    public function getQuestiontypes()
    {
        $qt = [];

        $qt["openended"]      = $GLOBALS['TL_LANG']['tl_survey_question']['openended'];
        $qt["multiplechoice"] = $GLOBALS['TL_LANG']['tl_survey_question']['multiplechoice'];
        $qt["matrix"]         = $GLOBALS['TL_LANG']['tl_survey_question']['matrix'];
        $qt["constantsum"]    = $GLOBALS['TL_LANG']['tl_survey_question']['constantsum'];

        return $qt;
    }

    public function getOpenEndedSubtypes()
    {
        $oe                  = [];
        $oe["oe_singleline"] = $GLOBALS['TL_LANG']['tl_survey_question']['oe_singleline'];
        $oe["oe_multiline"]  = $GLOBALS['TL_LANG']['tl_survey_question']['oe_multiline'];
        $oe["oe_integer"]    = $GLOBALS['TL_LANG']['tl_survey_question']['oe_integer'];
        $oe["oe_float"]      = $GLOBALS['TL_LANG']['tl_survey_question']['oe_float'];
        $oe["oe_date"]       = $GLOBALS['TL_LANG']['tl_survey_question']['oe_date'];
        $oe["oe_time"]       = $GLOBALS['TL_LANG']['tl_survey_question']['oe_time'];

        return $oe;
    }

    public function getMultipleChoiceSubtypes()
    {
        $mc                        = [];
        $mc["mc_singleresponse"]   = $GLOBALS['TL_LANG']['tl_survey_question']['mc_singleresponse'];
        $mc["mc_multipleresponse"] = $GLOBALS['TL_LANG']['tl_survey_question']['mc_multipleresponse'];
        $mc["mc_dichotomous"]      = $GLOBALS['TL_LANG']['tl_survey_question']['mc_dichotomous'];

        return $mc;
    }

    public function getMatrixSubtypes()
    {
        $mc                            = [];
        $mc["matrix_singleresponse"]   = $GLOBALS['TL_LANG']['tl_survey_question']['mc_singleresponse'];
        $mc["matrix_multipleresponse"] = $GLOBALS['TL_LANG']['tl_survey_question']['mc_multipleresponse'];

        return $mc;
    }

    public function setCompleteStatus(DataContainer $dc)
    {
        $this->Database->prepare("UPDATE tl_survey_question SET complete = ?, original = ? WHERE id=?")->execute(1, 1, $dc->id);
    }

    public function getMCStyleOptions(DataContainer $dc)
    {
        $objQuestion = $this->Database->prepare("SELECT multiplechoice_subtype FROM tl_survey_question WHERE id=?")->limit(1)->execute($dc->id);
        if (strcmp($objQuestion->multiplechoice_subtype, 'mc_multipleresponse') == 0) {
            return ['vertical', 'horizontal'];
        } else {
            return ['vertical', 'horizontal', 'select'];
        }
    }

    public function addScaleWizard(DataContainer $dc)
    {
        $objQuestion = $this->Database->prepare("SELECT multiplechoice_subtype FROM tl_survey_question WHERE id=?")->limit(1)->execute($dc->id);
        if (strcmp($objQuestion->multiplechoice_subtype, 'mc_singleresponse') == 0) {
            return '<a href="' . $this->addToUrl('key=scale') . '" title="' . specialchars($GLOBALS['TL_LANG']['tl_survey_question']['addscale'][1]) . '" onclick="Backend.getScrollOffset();">' . $this->generateImage('bundles/heimrichhannotcontaosurvey/img/scale.png', $GLOBALS['TL_LANG']['tl_survey_question']['addscale'][0], 'style="vertical-align:text-bottom;"') . '</a> <a href="'
                   . $this->addToUrl('key=scale') . '" title="' . specialchars($GLOBALS['TL_LANG']['tl_survey_question']['addscale'][1]) . '" onclick="Backend.getScrollOffset();">' . specialchars($GLOBALS['TL_LANG']['tl_survey_question']['addscale'][0]) . '</a>';
        }

        return '';
    }

    /**
     * Return a form to choose a CSV file and import it
     *
     * @param object
     *
     * @return string
     */
    public function addScale(DataContainer $dc)
    {
        if (\Input::get('key') != 'scale') {
            return '';
        }

        $objSurvey = $this->Database->prepare("SELECT tl_survey.language FROM tl_survey WHERE tl_survey.id=(SELECT pid FROM tl_survey_page WHERE tl_survey_page.id=(SELECT tl_survey_question.pid FROM tl_survey_question WHERE tl_survey_question.id=?))")->limit(1)->execute($dc->id);

        $objScales = $this->Database->prepare("SELECT tl_survey_scale.*, tl_survey_scale_folder.title AS folder FROM tl_survey_scale, tl_survey_scale_folder WHERE tl_survey_scale.language=? AND tl_survey_scale.pid = tl_survey_scale_folder.id ORDER BY tl_survey_scale_folder.title, tl_survey_scale.title")->execute($objSurvey->language);

        $arrScales = [];
        while ($objScales->next()) {
            $arrScales[$objScales->id] = ['title' => $objScales->title, 'scales' => deserialize($objScales->scale, true), 'folder' => $objScales->folder];
        }

        // Add scale
        if (\Input::post('FORM_SUBMIT') == 'tl_add_scale') {
            if ((!\Input::post('scale') || strcmp(\Input::post('scale'), '-') == 0)) {
                $_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['selectoption'];
                $this->reload();
            }

            $this->Database->prepare("UPDATE tl_survey_question SET choices=? WHERE id=?")->execute(serialize($arrScales[\Input::post('scale')]['scales']), $dc->id);

            setcookie('BE_PAGE_OFFSET', 0, 0, '/');
            $this->redirect(str_replace('&key=scale', '', \Environment::get('request')));
        }

        // Return form
        $result     = '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=scale', '', \Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['tl_survey_question']['addscale'][0] . '</h2>' . $this->getMessages() . '

<form action="' . ampersand(\Environment::get('request'), ENCODE_AMPERSANDS) . '" id="tl_add_scale" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_add_scale" />
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '" />

<div class="tl_tbox">
  <h3><label for="scale">' . $GLOBALS['TL_LANG']['tl_survey_question']['scale'][0] . '</label></h3>
  <select name="scale" id="scale" class="tl_select" onfocus="Backend.getScrollOffset();">
		<option value="-">-</option>\n';
        $lastfolder = "";
        foreach ($arrScales as $id => $scale) {
            if (strcmp($scale['folder'], $lastfolder) != 0) {
                if (strlen($lastfolder)) {
                    $result .= '</optgroup>';
                }
                $result .= '<optgroup label="' . specialchars($scale['folder']) . '">';
            }
            $result     .= '<option value="' . specialchars($id) . '">' . specialchars($scale['title']) . '</option>\n';
            $lastfolder = $scale['folder'];
        }
        $result .= '</optgroup>';
        $result .= '  </select>' . (strlen($GLOBALS['TL_LANG']['tl_survey_question']['scale'][1]) ? '
  <p class="tl_help">' . $GLOBALS['TL_LANG']['tl_survey_question']['scale'][1] . '</p>' : '') . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="add scale" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['tl_survey_question']['save_add_scale']) . '" /> 
</div>

</div>
</form>';

        return $result;
    }
}

