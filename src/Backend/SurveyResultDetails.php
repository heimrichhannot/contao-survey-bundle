<?php

namespace HeimrichHannot\SurveyBundle\Backend;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\SurveyBundle\Model\SurveyPageModel;
use HeimrichHannot\SurveyBundle\Model\SurveyQuestionModel;

/**
 * Class SurveyResultDetails
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyResultDetails extends Backend
{
    protected $blnSave = true;
    protected $useXLSX = false;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        $this->framework = System::getContainer()->get('contao.framework');
        $this->request   = System::getContainer()->get('huh.request');

        parent::__construct();
        if (in_array('php_excel', System::getContainer()->get('huh.utils.container')->getActiveBundles())) {
            $this->useXLSX = true;
        }
    }

    public function useXLSX()
    {
        return $this->useXLSX;
    }

    public function showDetails(DataContainer $dc)
    {
        if ($this->request->get('key') != 'details') {
            return '';
        }
        $this->Template           = new BackendTemplate('be_question_result_details');
        $this->Template->hasError = false;
        $qid                      = $this->request->get('id');
        $questionType             = $this->framework->getAdapter(SurveyQuestionModel::class)->findByPk($qid);
        if (null === $questionType) {
            $this->Template->hasError = true;
            $this->Template->error    = 'ERROR: No statistical data found!';
        }

        $surveyPage = $this->framework->getAdapter(SurveyPageModel::class)->findByPk($questionType->pid);
        if (null === $surveyPage) {
            $this->Template->hasError = true;
            $this->Template->error    = 'ERROR: No statistical data found!';
        }
        $class = "HeimrichHannot\SurveyBundle\Question\SurveyQuestion" . ucfirst($questionType->questiontype);
        $this->loadLanguageFile("tl_survey_result");
        $this->loadLanguageFile("tl_survey_question");
        $this->Template->back     = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->hrefBack = System::getContainer()->get('huh.utils.url')->getCurrentUrl(['skipParams' => true]) . '?do=' . $this->request->get('do') . '&amp;key=cumulated&amp;id=' . $surveyPage->pid;
        if (class_exists($class)) {
            $question                = new $class($qid);
            $this->Template->summary = $GLOBALS['TL_LANG']['tl_survey_result']['detailsSummary'];
            $this->Template->heading = sprintf($GLOBALS['TL_LANG']['tl_survey_result']['detailsHeading'], $qid);
            $data                    = [];
            array_push($data, ["key" => 'ID:', 'value' => $question->id, 'keyclass' => 'first', 'valueclass' => 'last']);
            array_push($data, ["key" => $GLOBALS['TL_LANG']['tl_survey_question']['questiontype'][0] . ':', 'value' => specialchars($GLOBALS['TL_LANG']['tl_survey_question'][$question->questiontype]), 'keyclass' => 'first tl_bg', 'valueclass' => 'last tl_bg']);
            array_push($data, ["key" => $GLOBALS['TL_LANG']['tl_survey_question']['title'][0] . ':', 'value' => $question->title, 'keyclass' => 'first', 'valueclass' => 'last']);
            array_push($data, ["key" => $GLOBALS['TL_LANG']['tl_survey_question']['question'][0] . ':', 'value' => $question->question, 'keyclass' => 'first tl_bg', 'valueclass' => 'last tl_bg']);
            array_push($data, ["key" => $GLOBALS['TL_LANG']['tl_survey_question']['answered'] . ':', 'value' => $question->statistics["answered"], 'keyclass' => 'first', 'valueclass' => 'last']);
            array_push($data, ["key" => $GLOBALS['TL_LANG']['tl_survey_question']['skipped'] . ':', 'value' => $question->statistics["skipped"], 'keyclass' => 'first tl_bg', 'valueclass' => 'last tl_bg']);
            array_push($data, ["key" => $GLOBALS['TL_LANG']['tl_survey_result']['answers'] . ':', 'value' => $question->getAnswersAsHTML(), 'keyclass' => 'first', 'valueclass' => 'last']);
            $this->Template->data = $data;
        } else {
            $this->Template->hasError = true;
            $this->Template->error    = 'ERROR: No statistical data found!';
        }

        return $this->Template->parse();
    }

    public function showCumulated(DataContainer $dc)
    {
        if ($this->request->get('key') != 'cumulated') {
            return '';
        }
        $this->loadLanguageFile('tl_survey_result');
        $this->loadLanguageFile('tl_survey_question');
        $surveyPages     = $this->framework->getAdapter(SurveyPageModel::class)->findBy('pid', $this->request->get('id'));
        $data            = [];
        $abs_question_no = 0;
        foreach ($surveyPages as $surveyPage) {

            $questionCollection = $this->framework->getAdapter(SurveyQuestionModel::class)->findBy('pid', $surveyPage->id);

            if (null === $questionCollection) {
                continue;
            }

            foreach ($questionCollection as $questionModel) {
                $abs_question_no++;
                $class = "HeimrichHannot\SurveyBundle\Question\SurveyQuestion" . ucfirst($questionModel->questiontype);
                if (class_exists($class)) {
                    $question       = new $class();
                    $question->data = $questionModel->row();
                    $strUrl         = System::getContainer()->get('huh.utils.url')->getCurrentUrl(['skipParams' => true]) . '?do=' . $this->request->get('do');
                    $strUrl         .= '&amp;key=details&amp;id=' . $question->id;
                    array_push($data, [
                        'number'       => $abs_question_no,
                        'title'        => specialchars($questionModel->title),
                        'type'         => specialchars($GLOBALS['TL_LANG']['tl_survey_question'][$questionModel->questiontype]),
                        'answered'     => $question->statistics["answered"],
                        'skipped'      => $question->statistics["skipped"],
                        'hrefdetails'  => $strUrl,
                        'titledetails' => specialchars(sprintf($GLOBALS['TL_LANG']['tl_survey_result']['details'][1], $question->id)),
                    ]);
                }
            }
        }
        $exporter                    = System::getContainer()->get('huh.exporter.action.backendexport')->getGlobalOperation('exportCumulated', $GLOBALS['TL_LANG']['tl_survey_result']['export']);
        $exporter['href']            = System::getContainer()->get('huh.utils.url')->getCurrentUrl(['skipParams' => true]) . '?do=' . $this->request->get('do') . '&id=' . $this->request->get('id') . '&' . $exporter['href'];
        $this->Template              = new BackendTemplate('be_survey_result_cumulated');
        $this->Template->back        = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $hrefExport                  = System::getContainer()->get('huh.utils.url')->getCurrentUrl(['skipParams' => true]) . '?do=' . $this->request->get('do');
        $this->Template->hrefBack    = $hrefExport;
        $hrefExport                  .= '&amp;key=export&amp;id=' . $this->request->get('id');
        $this->Template->export      = $exporter;
        $this->Template->hrefExport  = $hrefExport;
        $this->Template->heading     = specialchars($GLOBALS['TL_LANG']['tl_survey_result']['cumulatedResults']);
        $this->Template->summary     = 'cumulated results';
        $this->Template->data        = $data;
        $this->Template->imgdetails  = 'bundles/heimrichhannotcontaosurvey/img/details.png';
        $this->Template->lngAnswered = $GLOBALS['TL_LANG']['tl_survey_question']['answered'];
        $this->Template->lngSkipped  = $GLOBALS['TL_LANG']['tl_survey_question']['skipped'];

        return $this->Template->parse();
    }
}

