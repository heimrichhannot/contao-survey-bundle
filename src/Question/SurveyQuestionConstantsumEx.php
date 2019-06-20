<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\StringUtil;

/**
 * Class SurveyQuestionConstantsumEx
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionConstantsumEx extends SurveyQuestionConstantsum
{

    protected $choices = [];

    /**
     * Import String library via superclass.
     */
    public function __construct($questionId = 0)
    {
        parent::__construct($questionId);
    }

    /**
     * Exports constant sum question headers and all existing answers.
     *
     * Constant sum questions occupy one column for every choice / input field.
     * All choices are exported turned ccw in the header.
     * The answer values are given formatted numerically.
     * Common question headers, e.g. the id, question-numbers, title are exported in merged cells
     * spanning all subquestion columns.
     *
     * As a side effect the width for each column is calculated and set via the given $xls object.
     * Row height is currently calculated/set ONLY for the row with subquestions, which is turned
     * 90° ccw ... thus it is effectively also a text width calculation.
     *
     * Not setting row(/text) height explicitly in the general case is no problem in OpenOffice Calc 3.1,
     * which does a good job here by default. However Excel 95/97 seems to do it worse,
     * I can't test that currently. "Set optimal row height" might help users of Excel.
     *
     * @param int   &$row             row to put a cell in
     * @param int   &$col             col to put a cell in
     * @param array  $questionNumbers array with page and question numbers
     * @param array  $participants    array with all participant data
     *
     * @return array  the cells to be added to the export
     *
     */
    public function exportDetailsToExcel(&$row, &$col, $questionNumbers, $participants)
    {
        /*
        print "<pre>\n";
        var_export(deserialize($this->arrData['sumchoices'], true));
        foreach ($this->statistics['participants'] as $k => $v) {
            print "'$k' => ";
            var_export(deserialize($v[0]['result']));
            print "\n";
        }
        var_export($this->statistics);
        var_export($this->arrData);
        print "</pre>\n";
        die();
        */
        $valueCol    = $col;
        $rotateInfo  = [];
        $headerCells = $this->exportQuestionHeadersToExcel($row, $col, $questionNumbers, $rotateInfo);
        $resultCells = $this->exportDetailResults($row, $valueCol, $participants);

        return array_merge($headerCells, $resultCells);
    }

    /**
     * Exports the column headers for a question of type 'matrix'.
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
        $this->choices = deserialize($this->arrData['sumchoices'], true);
        foreach ($this->choices as $k => $v) {
            $this->choices[$k] = utf8_decode(StringUtil::decodeEntities($v));
        }
        $numcols = count($this->choices);

        $result = [];

        // ID and question numbers
        $result[] = [
            'row'  => $row,
            'col'  => $col,
            'data' => $this->id,
        ];
        $row++;
        $result[] = [
            'row'  => $row,
            'col'  => $col,
            'data' => $questionNumbers['abs_question_no'],
        ];
        $row++;
        $result[] = [
            'row'  => $row,
            'col'  => $col,
            'data' => $questionNumbers['page_no'] . '.' . $questionNumbers['rel_question_no'],
        ];
        $row++;

        // question type
        $result[] = [
            'row'  => $row,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_question'][$this->questiontype],
        ];
        $row++;

        // answered and skipped info, retrieves all answers as a side effect
        $result[] = [
            'row'  => $row,
            'col'  => $col,
            'data' => $this->statistics['answered'],
        ];
        $row++;
        $result[] = [
            'row'  => $row,
            'col'  => $col,
            'data' => $this->statistics['skipped'],
        ];
        $row++;

        // question title
        $title    = StringUtil::decodeEntities($this->title) . ($this->arrData['obligatory'] ? ' *' : '');
        $result[] = [
            'row'      => $row,
            'col'      => $col,
            'textwrap' => 1,
            'data'     => $title,
        ];
        // Guess a minimum column width for the title
        $row++;

        if ($numcols == 1) {
            // This is a strange case: a constant sum question with just one choice.
            // However, users do that (at least for testing) and have the right to do so.
            // Just add the one and only choice, without rotation ...
            $result[] = [
                'row'  => $row,
                'col'  => $col,
                'data' => $this->choices[0],
            ];

            // ... and recalculate the col width
            $col++;
        } else {
            // output all choice columns
            $rotateInfo[$row] = [];
            foreach ($this->choices as $key => $choice) {
                $result[] = [
                    'row'  => $row,
                    'col'  => $col,
                    'data' => $choice,
                ];
                // make cols as narrow as possible, but wide enough for the title in the merged cells above
                $rotateInfo[$row][$col] = $choice;
                $col++;
            }
        }
        $row++;

        return $result;
    }

    /**
     * Exports all results/answers to the question at hand.
     *
     * Sets some column widthes as a side effect.
     *
     * @param object &$xls          the excel object to call methods on
     * @param string  $sheet        name of the worksheet
     * @param int    &$row          row to put a cell in
     * @param int    &$col          col to put a cell in
     * @param array   $participants array with all participant data
     *
     * @return array  the cells to be added to the export
     */
    protected function exportDetailResults(&$row, &$col, $participants)
    {
        $cells    = [];
        $startCol = $col;
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
                $col        = $startCol;
                $arrAnswers = deserialize($data, true);
                foreach ($this->choices as $k => $choice) {
                    $strAnswer = '';
                    if (array_key_exists($k + 1, $arrAnswers)) {
                        $strAnswer = $arrAnswers[$k + 1];
                    }
                    if (strlen($strAnswer)) {
                        // Set value to numeric, when the coices are e.g. school grades '1'-'5', a common case (for me).
                        // Then the user is able to work with formulars in Excel/Calc, avarage for instance.
                        $cells[] = [
                            'row'  => $row,
                            'col'  => $col,
                            'data' => $strAnswer,
                        ];
                    }
                    $col++;
                }
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
