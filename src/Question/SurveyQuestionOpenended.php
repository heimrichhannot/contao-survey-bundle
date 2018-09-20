<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\System;
use HeimrichHannot\SurveyBundle\Model\SurveyResultModel;

/**
 * Class SurveyQuestionOpenended
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionOpenended extends SurveyQuestion
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
            }
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
        $col = 1;
        if (is_array($this->statistics["answers"])) {
            foreach ($this->statistics["answers"] as $answer) {
                array_push($result, ["row" => $row, "col" => $col++, "data" => $answer]);
            }
        }
        $row++;
        $row++;
        array_push($result, ["row" => $row, "col" => 0, "data" => '']);
        $row++;

        return $result;
    }

    public function __set($name, $value)
    {
        switch ($name) {
            default:
                parent::__set($name, $value);
                break;
        }
    }
}

