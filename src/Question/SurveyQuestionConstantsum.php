<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\FrontendTemplate;
use Contao\StringUtil;

/**
 * Class SurveyQuestionConstantsum
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionConstantsum extends SurveyQuestion
{
    /**
     * Import String library
     */
    public function __construct($questionId = 0)
    {
        parent::__construct($questionId);
    }

    protected function calculateStatistics()
    {
        if (array_key_exists("id", $this->arrData) && array_key_exists("parentID", $this->arrData)) {
            $objResult = $this->Database->prepare("SELECT * FROM tl_survey_result WHERE qid=? AND pid=?")->execute($this->arrData["id"], $this->arrData["parentID"]);
            if ($objResult->numRows) {
                $this->calculateAnsweredSkipped($objResult);
                $this->calculateCumulated();
            }
        }
    }

    protected function calculateCumulated()
    {
        $cumulated          = [];
        $cumulated['other'] = [];
        foreach ($this->arrStatistics["answers"] as $answer) {
            $arrAnswer = StringUtil::deserialize($answer, true);
            if (is_array($arrAnswer)) {
                foreach ($arrAnswer as $answerkey => $answervalue) {
                    $cumulated[$answerkey][$answervalue]++;
                }
            }
        }
        foreach ($cumulated as $key => $value) {
            ksort($value);
            $cumulated[$key] = $value;
        }
        $this->arrStatistics['cumulated'] = $cumulated;
    }

    public function getAnswersAsHTML()
    {
        if (is_array($this->statistics["cumulated"])) {
            $template                 = new FrontendTemplate('survey_answers_constantsum');
            $template->choices        = deserialize($this->arrData['sumchoices'], true);
            $template->summary        = $GLOBALS['TL_LANG']['tl_survey_result']['cumulatedSummary'];
            $template->answer         = $GLOBALS['TL_LANG']['tl_survey_result']['answer'];
            $template->nrOfSelections = $GLOBALS['TL_LANG']['tl_survey_result']['nrOfSelections'];
            $template->cumulated      = $this->statistics["cumulated"];

            return $template->parse();
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            default:
                parent::__set($name, $value);
                break;
        }
    }

    public function exportDataToExcel($sheet, &$row)
    {
        $result = [];
        array_push($result, ["row" => $row, "col" => 0, "data" => "ID"]);
        array_push($result, ["row" => $row, "col" => 1, "data" => $this->id]);
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_question']['questiontype'][0]]);
        array_push($result, ["row" => $row, "col" => 1, "data" => $GLOBALS['TL_LANG']['tl_survey_question'][$this->questiontype]]);
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_question']['title'][0]]);
        array_push($result, ["row" => $row, "col" => 1, "data" => $this->title]);
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_question']['question'][0]]);
        array_push($result, ["row" => $row, "col" => 1, "data" => strip_tags($this->question)]);
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_question']['answered']]);
        array_push($result, ["row" => $row, "col" => 1, "data" => $this->statistics["answered"]]);
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_question']['skipped']]);
        array_push($result, ["row" => $row, "col" => 1, "data" => $this->statistics["skipped"]]);
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_result']['answers']]);

        if (is_array($this->statistics["cumulated"])) {
            $arrChoices = deserialize($this->arrData['sumchoices'], true);
            $counter    = 1;
            foreach ($arrChoices as $id => $choice) {
                array_push($result, ["row" => $row + $counter - 1, "col" => 1, "data" => $choice]);
                $counter += 2;
            }
            $counter = 1;
            $idx     = 1;
            foreach ($arrChoices as $id => $choice) {
                $acounter = 2;
                foreach ($this->statistics["cumulated"][$idx] as $answervalue => $nrOfAnswers) {
                    array_push($result, ["row" => $row + $counter - 1, "col" => $acounter, "data" => $answervalue]);
                    array_push($result, ["row" => $row + $counter, "col" => $acounter, "data" => (($nrOfAnswers) ? $nrOfAnswers : 0)]);
                    $acounter++;
                }
                $idx++;
                $counter += 2;
            }
            $row += count($arrChoices) * 2 + 1;
        }
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => '']);
        $row++;

        return $result;
    }
}

