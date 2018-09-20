<?php

/**
 * BACK END FORM FIELDS
 */
\Contao\System::getContainer()->get('huh.utils.array')->insertInArrayByName($GLOBALS['BE_MOD'], 'design', [
    "surveys" => [
        "survey" => [
            "tables"          => [
                "tl_survey",
                "tl_survey_page",
                "tl_survey_question",
                "tl_survey_participant",
                "tl_survey_pin_tan",
            ],
            'scale'           => ['tl_survey_question', 'addScale'],
            'exportCumulated' => ['huh.exporter.action.backendexport', 'export'],
            'createtan'       => ['HeimrichHannot\SurveyBundle\Backend\SurveyPINTAN', 'createTAN'],
            'cumulated'       => ['HeimrichHannot\SurveyBundle\Backend\SurveyResultDetails', 'showCumulated'],
            'details'         => ['HeimrichHannot\SurveyBundle\Backend\SurveyResultDetails', 'showDetails'],
            'exporttan'       => ['huh.exporter.action.backendexport', 'export'],
            'exportDetails'   => ['huh.exporter.action.backendexport', 'export'],
            'icon'            => 'bundles/heimrichhannotcontaosurvey/img/survey.png',
        ],
        "scale"  => [
            "tables" => [
                "tl_survey_scale_folder",
                "tl_survey_scale",
            ],
            'icon'   => 'bundles/heimrichhannotcontaosurvey/img/scale.png',
        ],
    ],
]);

// Content elements
$GLOBALS['TL_CTE']['includes']['survey'] = 'HeimrichHannot\SurveyBundle\ContentElement\ContentSurvey';

/**
 * CSS
 */
if (\Contao\System::getContainer()->get('huh.utils.container')->isBackend()) {
    $GLOBALS['TL_CSS']['survey'] = 'bundles/heimrichhannotcontaosurvey/css/survey.min.css';
}

/**
 * JAVASCRIPT
 */
if (\Contao\System::getContainer()->get('huh.utils.container')->isFrontend()) {
    $GLOBALS['TL_CSS']['survey'] = 'bundles/heimrichhannotcontaosurvey/js/survey.min.js';
}

$GLOBALS['TL_SVY']['openended']      = 'HeimrichHannot\SurveyBundle\Form\FormOpenEndedQuestion';
$GLOBALS['TL_SVY']['multiplechoice'] = 'HeimrichHannot\SurveyBundle\Form\FormMultipleChoiceQuestion';
$GLOBALS['TL_SVY']['matrix']         = 'HeimrichHannot\SurveyBundle\Form\FormMatrixQuestion';
$GLOBALS['TL_SVY']['constantsum']    = 'HeimrichHannot\SurveyBundle\Form\FormConstantSumQuestion';

/**
 * Set the member URL parameter as url keyword
 */
$GLOBALS['TL_CONFIG']['urlKeywords'] .= (strlen(trim($GLOBALS['TL_CONFIG']['urlKeywords'])) ? ',' : '') . "code";

/**
 * Register models
 */
$GLOBALS['TL_MODELS']['tl_survey']             = 'HeimrichHannot\SurveyBundle\Model\SurveyModel';
$GLOBALS['TL_MODELS']['tl_survey_page']        = 'HeimrichHannot\SurveyBundle\Model\SurveyPageModel';
$GLOBALS['TL_MODELS']['tl_survey_participant'] = 'HeimrichHannot\SurveyBundle\Model\SurveyParticipantModel';
$GLOBALS['TL_MODELS']['tl_survey_pin_tan']     = 'HeimrichHannot\SurveyBundle\Model\SurveyPinTanModel';
$GLOBALS['TL_MODELS']['tl_survey_question']    = 'HeimrichHannot\SurveyBundle\Model\SurveyQuestionModel';
$GLOBALS['TL_MODELS']['tl_survey_result']      = 'HeimrichHannot\SurveyBundle\Model\SurveyResultModel';
