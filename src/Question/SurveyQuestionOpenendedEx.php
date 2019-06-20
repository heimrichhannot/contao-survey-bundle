<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\StringUtil;

/**
 * Class SurveyQuestionOpenendedEx
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionOpenendedEx extends SurveyQuestionOpenended
{

    /**
     * Import String library via superclass.
     */
    public function __construct($questionId = 0)
    {
        parent::__construct($questionId);
    }

    /**
     * Exports question headers and all existing answers.
     *
     * As a side effect the width for each column is calculated and set via the given $xls object.
     * Row height is currently calculated/set ONLY for the row with subquestions/choices (neccessary
     * for matrix questions etc, here only test strings are given out for the testing), which is turned
     * 90° ccw ... thus it is effectively also a text width calculation.
     *
     * Not setting row(/text) height explicitly in the general case is no problem in OpenOffice Calc 3.1,
     * which does a good job here by default. However Excel 95/97 seems to do it worse,
     * I can't test that currently. "Set optimal row height" might help users of Excel.
     *
     * @param object &$xls             the excel object to call methods on
     * @param string  $sheet           name of the worksheet
     * @param int    &$row             row to put a cell in
     * @param int    &$col             col to put a cell in
     * @param array   $questionNumbers array with page and question numbers
     * @param array   $participants    array with all participant data
     *
     * @return array  the cells to be added to the export
     */
    public function exportDetailsToExcel(&$row, &$col, $questionNumbers, $participants)
    {
        $rotateInfo  = [];
        $headerCells = $this->exportQuestionHeadersToExcel($row, $col, $questionNumbers, $rotateInfo);
        $resultCells = $this->exportDetailResults($row, $col, $participants);

        $col++;

        return array_merge($headerCells, $resultCells);
    }

    /**
     * Exports the column headers for a question of type 'openended' and currently has some test code.
     *
     * Several rows are returned, so that the user of the Excel file is able to
     * use them for reference, filtering and sorting.
     *
     * @param int   &$row             in/out row to put a cell in
     * @param int   &$col             in/out col to put a cell in
     * @param array  $questionNumbers array with page and question numbers
     * @param array &$rotateInfo      out param with row => text for later calculation of row height
     *
     * @return array  the cells to be added to the export
     */
    protected function exportQuestionHeadersToExcel(&$row, &$col, $questionNumbers, &$rotateInfo)
    {
        $result = [];

        // ID and question numbers
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $this->id,
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $questionNumbers['abs_question_no'],
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $questionNumbers['page_no'] . '.' . $questionNumbers['rel_question_no'],
        ];

        // question type
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_question'][$this->questiontype],
        ];

        // answered and skipped info, retrieves all answers as a side effect
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $this->statistics['answered'],
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $this->statistics['skipped'],
        ];

        // question title
        $title    = StringUtil::decodeEntities($this->title) . ($this->arrData['obligatory'] ? ' *' : '');
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $title,
        ];
        // empty cell used in other question types, for the formatting
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => '',
        ];

        return $result;
    }

    /**
     * Exports all results/answers to the question at hand.
     *
     * Sets column widthes as a side effect.
     *
     * @param int   &$row          row to put a cell in
     * @param int   &$col          col to put a cell in
     * @param array  $participants array with all participant data
     *
     * @return array  the cells to be added to the export
     */
    protected function exportDetailResults(&$row, &$col, $participants)
    {
        $cells = [];
        foreach ($participants as $key => $value) {
            $data = false;
            if (isset($this->statistics['participants'][$key]['result'])) {
                // future state of survey_ce
                $data = $this->statistics['participants'][$key]['result'];
            } elseif (isset($this->statistics['participants'][$key][0]['result'])) {
                // current state of survey_ce: additional subarray with always 1 entry
                $data = $this->statistics['participants'][$key][0]['result'];
            }
            if ($data) {
                $cells[] = [
                    'row'  => $row,
                    'col'  => $col,
                    'data' => StringUtil::decodeEntities($data),
                ];
            } else {
                // add an empty cell bc formatting
                $cells[] = [
                    'row'  => $row,
                    'col'  => $col,
                    'data' => '',
                ];
            }
            $row++;
        }

        return $cells;
    }
}
