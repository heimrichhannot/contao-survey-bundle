<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\SurveyBundle\Model\SurveyResultModel;

/**
 * Class SurveyQuestionMultiplechoice
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionMultiplechoice extends SurveyQuestion
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
            $results = System::getContainer()->get('contao.framework')->getAdapter(SurveyResultModel::class)->findBy(['qid=?', 'pid=?'], [$this->arrData["id"], $this->arrData["parentID"]]);
            if (null !== $results) {
                $this->calculateAnsweredSkipped($results);
                $this->calculateCumulated();
            }
        }
    }

    protected function calculateAnsweredSkipped(&$objResult)
    {
        $this->arrStatistics             = [];
        $this->arrStatistics["answered"] = 0;
        $this->arrStatistics["skipped"]  = 0;
        while ($objResult->next()) {
            $id                                         = (strlen($objResult->pin)) ? $objResult->pin : $objResult->uid;
            $this->arrStatistics["participants"][$id][] = $objResult->row();
            $this->arrStatistics["answers"][]           = $objResult->result;
            if (strlen($objResult->result)) {
                $arrAnswer = StringUtil::deserialize($objResult->result, true);
                $found     = false;
                if (is_array($arrAnswer['value'])) {
                    foreach ($arrAnswer['value'] as $answervalue) {
                        if (strlen($answervalue)) {
                            $found = true;
                        }
                    }
                } else {
                    if (strlen($arrAnswer["value"])) {
                        $found = true;
                    }
                }
                if (strlen($arrAnswer['other'])) {
                    $found = true;
                }
                if ($found) {
                    $this->arrStatistics["answered"]++;
                } else {
                    $this->arrStatistics["skipped"]++;
                }
            } else {
                $this->arrStatistics["skipped"]++;
            }
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

    protected function calculateCumulated()
    {
        $cumulated          = [];
        $cumulated['other'] = [];
        foreach ($this->arrStatistics["answers"] as $answer) {
            $arrAnswer = deserialize($answer, true);
            if (is_array($arrAnswer['value'])) {
                foreach ($arrAnswer['value'] as $answervalue) {
                    if (strlen($answervalue)) {
                        $cumulated[$answervalue]++;
                    }
                }
            } else {
                if (strlen($arrAnswer['value'])) {
                    $cumulated[$arrAnswer['value']]++;
                }
            }
            if (strlen($arrAnswer['other'])) {
                array_push($cumulated['other'], $arrAnswer['other']);
            }
        }
        $this->arrStatistics['cumulated'] = $cumulated;
    }

    public function getAnswersAsHTML()
    {
        if (is_array($this->statistics["cumulated"])) {
            $template                 = new FrontendTemplate('survey_answers_multiplechoice');
            $template->statistics     = $this->statistics;
            $template->summary        = $GLOBALS['TL_LANG']['tl_survey_result']['cumulatedSummary'];
            $template->answer         = $GLOBALS['TL_LANG']['tl_survey_result']['answer'];
            $template->nrOfSelections = $GLOBALS['TL_LANG']['tl_survey_result']['nrOfSelections'];
            $template->choices        = (strcmp($this->arrData['multiplechoice_subtype'], 'mc_dichotomous') != 0) ? deserialize($this->arrData['choices'], true) : [0 => $GLOBALS['TL_LANG']['tl_survey_question']['yes'], 1 => $GLOBALS['TL_LANG']['tl_survey_question']['no']];
            $template->other          = ($this->arrData['addother']) ? true : false;
            $template->othertitle     = specialchars($this->arrData['othertitle']);
            $otherchoices             = [];
            if (count($this->statistics['cumulated']['other'])) {
                foreach ($this->statistics['cumulated']['other'] as $value) {
                    $otherchoices[specialchars($value)]++;
                }
            }
            $template->otherchoices = $otherchoices;

            return $template->parse();
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
        array_push($result, ["row" => $row + 1, "col" => 0, "data" => $GLOBALS['TL_LANG']['tl_survey_result']['nrOfSelections']]);
        $arrChoices = (strcmp($this->arrData['multiplechoice_subtype'], 'mc_dichotomous') != 0) ? deserialize($this->arrData['choices'], true) : [0 => $GLOBALS['TL_LANG']['tl_survey_question']['yes'], 1 => $GLOBALS['TL_LANG']['tl_survey_question']['no']];
        $col        = 1;
        foreach ($arrChoices as $id => $choice) {
            array_push($result, ["row" => $row, "col" => $col, "data" => $choice]);
            array_push($result, ["row" => $row + 1, "col" => $col++, "data" => (($this->statistics['cumulated'][$id + 1]) ? $this->statistics['cumulated'][$id + 1] : 0)]);
        }
        if ($this->arrData['addother']) {
            array_push($result, ["row" => $row, "col" => $col, "data" => $this->arrData['othertitle']]);
            array_push($result, ["row" => $row + 1, "col" => $col++, "data" => count($this->statistics['cumulated']['other'])]);
            if (count($this->statistics['cumulated']['other'])) {
                $otherchoices = [];
                foreach ($this->statistics['cumulated']['other'] as $value) {
                    $otherchoices[$value]++;
                }
                foreach ($otherchoices as $key => $count) {
                    array_push($result, ["row" => $row, "col" => $col, "data" => $key, "bgcolor" => $this->otherbackground, "color" => $this->othercolor]);
                    array_push($result, ["row" => $row + 1, "col" => $col++, "data" => $count]);
                }
            }
        }
        $row++;
        $row++;
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => '']);
        $row++;

        return $result;
    }


}

