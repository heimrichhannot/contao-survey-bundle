<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\Backend;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\System;
use HeimrichHannot\SurveyBundle\Model\SurveyQuestionModel;

/**
 * Class SurveyQuestion
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
abstract class SurveyQuestion
{
    protected $arrData;
    protected $arrStatistics;

    /**
     * Import String library
     */
    public function __construct($questionId = 0)
    {
        $this->objQuestion               = null;
        $this->arrStatistics             = [];
        $this->arrStatistics["answered"] = 0;
        $this->arrStatistics["skipped"]  = 0;
        if ($questionId > 0) {
            $question = System::getContainer()->get('contao.framework')->getAdapter(SurveyQuestionModel::class)->findSurveyPageTitleAndQuestionById($questionId);
            if (null !== $question) {
                $this->data = $question;
            }
        }
    }

    protected abstract function calculateStatistics();

    protected function calculateAnsweredSkipped(&$results)
    {
        $this->arrStatistics             = [];
        $this->arrStatistics["answered"] = 0;
        $this->arrStatistics["skipped"]  = 0;
        foreach ($results as $result) {
            $id                                         = (strlen($result->pin)) ? $result->pin : $result->uid;
            $this->arrStatistics["participants"][$id][] = $result->row();
            $this->arrStatistics["answers"][]           = $result->result;
            if (strlen($result->result)) {
                $this->arrStatistics["answered"]++;
            } else {
                $this->arrStatistics["skipped"]++;
            }
        }
    }

    public function getAnswersAsHTML()
    {
        if (is_array($this->statistics["answers"])) {
            $template          = new FrontendTemplate('survey_answers_default');
            $template->answers = $this->statistics['answers'];

            return $template->parse();
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case "data":
                if (is_array($value)) {
                    $this->arrData =& $value;
                }
                break;
            default:
                $this->$name = $value;
                break;
        }
    }

    public function clearStatistics()
    {
        $this->arrStatistics = [];
    }

    public function __get($name)
    {
        switch ($name) {
            case "statistics":
                if (count($this->arrStatistics) <= 2) {
                    $this->calculateStatistics();
                }

                return $this->arrStatistics;
                break;
            case "id":
            case "title":
            case "question":
            case "questiontype":
                return $this->arrData[$name];
                break;
            case "titlebgcolor":
                return "#C0C0C0";
            case "titlecolor":
                return "#000000";
            case "otherbackground":
                return "#FFFFCC";
            case "othercolor":
                return "#000000";
            default:
                return $this->$name;
                break;
        }
    }

    public function exportDataToExcel($sheet, &$row)
    {
        // overwrite in parent classes
        return [];
    }
}

