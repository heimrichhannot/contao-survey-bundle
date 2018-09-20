<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\StringUtil;

/**
 * Class SurveyQuestionMatrixEx
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionMatrixEx extends SurveyQuestionMatrix
{

    protected $subquestions = [];
    protected $choices      = [];

    /**
     * Import String library via superclass.
     */
    public function __construct($questionId = 0)
    {
        parent::__construct($questionId);
    }

    /**
     * Exports matrix question headers and all existing answers.
     *
     * Matrix questions currently occupy one column for every matrix row / subquestion, which
     * is given out turned ccw in the header, regardless of the subtype single/multiple choice.
     * This is so to avoid excessive numbers of columns for the multiple choice subtype (num rows * num cols).
     * Instead the value cells carry the choice/s (matrix col names), either a single value
     * (single choice) or a delimiter '|' separated list of them (multiple choice).
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
     * @param int   &$row            row to put a cell in
     * @param int   &$col            col to put a cell in
     * @param array $questionNumbers array with page and question numbers
     * @param array $participants    array with all participant data
     *
     * @return array  the cells to be added to the export
     *
     */
    public function exportDetailsToExcel(&$row, &$col, $questionNumbers, $participants)
    {
        /*
        print "<pre>\n";
        var_export(deserialize($this->arrData['matrixrows'], true));
        var_export(deserialize($this->arrData['matrixcolumns'], true));
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
     * @param int   &$row            in/out row to put a cell in
     * @param int   &$col            in/out col to put a cell in
     * @param array $questionNumbers array with page and question numbers
     * @param array &$rotateInfo     out param with row => text for later calculation of row height
     *
     * @return array  the cells to be added to the export
     */
    protected function exportQuestionHeadersToExcel(&$row, &$col, $questionNumbers, &$rotateInfo)
    {
        $this->subquestions = deserialize($this->arrData['matrixrows'], true);
        foreach ($this->subquestions as $k => $v) {
            $this->subquestions[$k] = StringUtil::decodeEntities($v);
        }
        $numcols = count($this->subquestions);

        $this->choices = deserialize($this->arrData['matrixcolumns'], true);
        if ($this->arrData['addneutralcolumn']) {
            $this->choices[] = '-';
        }
        foreach ($this->choices as $k => $v) {
            $this->choices[$k] = StringUtil::decodeEntities($v);
        }

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
            'data' => $GLOBALS['TL_LANG']['tl_survey_question'][$this->questiontype] . ', ' . $GLOBALS['TL_LANG']['tl_survey_question'][$this->arrData['matrix_subtype']],
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
            'row'  => $row,
            'col'  => $col,
            'data' => $title,
        ];
        // Guess a minimum column width for the title
        $row++;

        if ($numcols == 1) {
            // This is a strange case: a matrix question with just one subquestion.
            // However, users do that (at least for testing) and have the right to do so.
            // Just add the one and only subquestion, without rotation ...
            $result[] = [
                'row'  => $row,
                'col'  => $col,
                'data' => $this->subquestions[0],
            ];

            // ... and recalculate the col width
            $col++;
        } else {
            // output all subquestion columns
            $rotateInfo[$row] = [];
            foreach ($this->subquestions as $key => $subquestion) {
                $result[] = [
                    'row'  => $row,
                    'col'  => $col,
                    'data' => $subquestion,
                ];
                // make cols as narrow as possible, but wide enough for the title in the merged cells above
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
     * @param int   &$row         row to put a cell in
     * @param int   &$col         col to put a cell in
     * @param array $participants array with all participant data
     *
     * @return array  the cells to be added to the export
     */
    protected function exportDetailResults(&$row, &$col, $participants)
    {
        $cells    = [];
        $startCol = $col;
        foreach ($participants as $key => $value) {
            $data = false;
            if (strlen($this->statistics['participants'][$key]['result'])) {
                // future state of survey_ce
                $data = $this->statistics['participants'][$key]['result'];
            } elseif (strlen($this->statistics['participants'][$key][0]['result'])) {
                // current state of survey_ce: additional subarray with always 1 entry
                $data = $this->statistics['participants'][$key][0]['result'];
            }
            if ($data) {
                $col        = $startCol;
                $arrAnswers = deserialize($data, true);
                if ($this->arrData['matrix_subtype'] == 'matrix_singleresponse') {
                    foreach ($this->subquestions as $k => $junk) {
                        $strAnswer = '';
                        if (array_key_exists($k + 1, $arrAnswers)) {
                            // These 1 based array keys and values Helmut used here for the answers drive me crazy!
                            // 1 based would be perfectly OK in e.g. Erlang, where almost everything is 1 based,
                            // but not in PHP, where numerical arrays are 0 based typically.
                            $choice_key = $arrAnswers[$k + 1] - 1;
                            if (array_key_exists($choice_key, $this->choices)) {
                                $strAnswer = $this->choices[$choice_key];
                            }
                        }
                        if (strlen($strAnswer)) {
                            // Set value to numeric, when the coices are e.g. school grades '1'-'5', a common case (for me).
                            // Then the user is able to work with formulars in Excel/Calc, avarage for instance.
                            $cells[] = [
                                'row'  => $row,
                                'col'  => $col,
                                'data' => $strAnswer,
                            ];
                            // Guess a minimum column width for the answer column.
                        }
                        $col++;
                    }
                } elseif ($this->arrData['matrix_subtype'] == 'matrix_multipleresponse') {
                    foreach ($this->subquestions as $k => $junk) {
                        $strAnswer = '';
                        if (is_array($arrAnswers[$k + 1])) {
                            $arrTmp = [];
                            foreach ($arrAnswers[$k + 1] as $kk => $v) {
                                $arrTmp[] = $this->choices[$kk - 1];
                            }
                            $strAnswer = implode(' | ', $arrTmp);
                        }
                        if (strlen($strAnswer)) {
                            $cells[] = [
                                'row'  => $row,
                                'col'  => $col,
                                'data' => $strAnswer,
                            ];
                        }
                        $col++;
                    }
                }
            }
            $row++;
        }

        return $cells;
    }
}
