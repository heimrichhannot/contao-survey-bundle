<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\SurveyBundle\Exporter;


use Contao\Database;
use Contao\System;
use HeimrichHannot\ContaoExporterBundle\Exporter\Concrete\ExcelExporter;

class CumulatedResultsExporter extends ExcelExporter
{
    public function getEntities($pid)
    {
        $surveyId = System::getContainer()->get('huh.request')->get('id');
        $dbResult = $this->framework->createInstance(Database::class)->prepare("SELECT tl_survey_question.*, tl_survey_page.title as pagetitle, tl_survey_page.pid as parentID FROM tl_survey_question, tl_survey_page WHERE tl_survey_question.pid = tl_survey_page.id AND tl_survey_page.pid = ? ORDER BY tl_survey_page.sorting, tl_survey_question.sorting")->execute($surveyId);

        return $dbResult;
    }

    /**
     * @param $databaseResult
     *
     * @return array
     */
    public function exportList($databaseResult)
    {
        $formattedRows = [];
        if (0 === $databaseResult->numRows) {
            return $formattedRows;
        }

        System::loadLanguageFile('tl_survey_result');
        System::loadLanguageFile('tl_survey_question');
        $intRowCounter = 0;
        $sheet         = utf8_decode($GLOBALS['TL_LANG']['tl_survey_result']['cumulatedResults']);

        while ($databaseResult->next()) {
            $row   = $databaseResult->row();
            $class = "HeimrichHannot\SurveyBundle\Question\SurveyQuestion" . ucfirst($row["questiontype"]);
            if (class_exists($class)) {
                $question       = new $class();
                $question->data = $row;
                $cells          = $question->exportDataToExcel($sheet, $intRowCounter);
                foreach ($cells as $cell) {
                    $formattedRows[$cell['row']][$cell['col']] = $cell['data'];
                }
            }
        }

        return $formattedRows;
    }
}