<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\FrontendTemplate;

/**
 * Class SurveyQuestionMatrix
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionMatrix extends SurveyQuestion
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
            $arrAnswer = deserialize($answer, true);
            if (is_array($arrAnswer)) {
                foreach ($arrAnswer as $row => $answervalue) {
                    if (is_array($answervalue)) {
                        foreach ($answervalue as $singleanswervalue) {
                            $cumulated[$row][$singleanswervalue]++;
                        }
                    } else {
                        $cumulated[$row][$answervalue]++;
                    }
                }
            }
        }
        $this->arrStatistics['cumulated'] = $cumulated;
    }

    public function getAnswersAsHTML()
    {
        if (is_array($this->statistics["cumulated"])) {
            $template                 = new FrontendTemplate('survey_answers_matrix');
            $template->choices        = deserialize($this->arrData['matrixcolumns'], true);
            $template->rows           = deserialize($this->arrData['matrixrows'], true);
            $template->statistics     = $this->statistics;
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
            $arrRows     = deserialize($this->arrData['matrixrows'], true);
            $arrChoices  = deserialize($this->arrData['matrixcolumns'], true);
            $row_counter = 1;
            foreach ($arrRows as $id => $rowdata) {
                array_push($result, ["row" => $row + $row_counter, "col" => 1, "data" => $rowdata]);
                $row_counter++;
            }

            $row_counter = 1;
            foreach ($arrRows as $id => $rowdata) {
                $col_counter = 1;
                foreach ($arrChoices as $choiceid => $choice) {
                    if ($row_counter == 1) {
                        array_push($result, ["row" => $row, "col" => 1 + $col_counter, "data" => $choice]);
                    }
                    array_push($result, ["row" => $row + $row_counter, "col" => 1 + $col_counter, "data" => (($this->statistics['cumulated'][$row_counter][$col_counter]) ? $this->statistics['cumulated'][$row_counter][$col_counter] : 0)]);
                    $col_counter++;
                }
                $row_counter++;
            }

            $row += count($arrRows);
        }
//        $row += 2;
        $row++;
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => '']);
        $row++;

        return $result;
    }
}

