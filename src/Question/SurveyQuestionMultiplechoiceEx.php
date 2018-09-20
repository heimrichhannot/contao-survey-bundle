<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\StringUtil;

/**
 * Class SurveyQuestionMultiplechoiceEx
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionMultiplechoiceEx extends SurveyQuestionMultiplechoice
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
     * Exports multiple choice question headers and all existing answers.
     *
     * Questions of subtype mc_dichotomous occupy one column and get yes/no values as answers.
     *
     * Questions of subtype mc_singleresponse also occupy one column only and the participants
     * choice as answer. If there is the optinal "other answer" present and choosen, the value
     * will be the participants input prepended by the title of the other answer.
     *
     * Questions of subtype mc_multipleresponse occupy one column for every choice.
     * All possible coices are given in the header (turned ccw) and 'x' will be the value, if
     * choosen by the participant. The optional "other answer" gets its own column with the
     * participants entry as value. Common question headers, e.g. the id, question-numbers,
     * title are exported in merged cells spanning all choice columns.
     *
     * As a side effect the width for each column is calculated and set via the given $xls object.
     * Row height is currently calculated/set ONLY for the row with subquestions/choices, which is turned
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
     */
    public function exportDetailsToExcel(&$row, &$col, $questionNumbers, $participants)
    {
        $valueCol    = $col;
        $rotateInfo  = [];
        $headerCells = $this->exportQuestionHeadersToExcel($row, $col, $questionNumbers, $rotateInfo);
        $resultCells = $this->exportDetailResults($row, $valueCol, $participants);

        return array_merge($headerCells, $resultCells);
    }

    /**
     * Exports the column headers for a question of type 'multiple choice'.
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
        $this->choices = ($this->arrData['multiplechoice_subtype'] == 'mc_dichotomous') ? [
            0 => $GLOBALS['TL_LANG']['tl_survey_question']['yes'],
            1 => $GLOBALS['TL_LANG']['tl_survey_question']['no'],
        ] : deserialize($this->arrData['choices'], true);
        if ($this->arrData['addother']) {
            $this->choices[] = preg_replace('/[-=>:\s]+$/', '', $this->arrData['othertitle']);
        }
        $numcols = ($this->arrData['multiplechoice_subtype'] == 'mc_multipleresponse') ? count($this->choices) : 1;

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
            'data' => $GLOBALS['TL_LANG']['tl_survey_question'][$this->questiontype] . ', ' . $GLOBALS['TL_LANG']['tl_survey_question'][$this->arrData['multiplechoice_subtype']],
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
            // add an empty cell, just for the formatting
            $result[] = [
                'row'  => $row,
                'col'  => $col,
                'data' => '',
            ];
            $col++;
        } else {
            // output all choice columns
            $rotateInfo[$row] = [];
            foreach ($this->choices as $key => $choice) {
                $result[]               = [
                    'row'  => $row,
                    'col'  => $col,
                    'data' => $choice,
                ];
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
     * @param object &$xls         the excel object to call methods on
     * @param string $sheet        name of the worksheet
     * @param int    &$row         row to put a cell in
     * @param int    &$col         col to put a cell in
     * @param array  $participants array with all participant data
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
                if ($this->arrData['multiplechoice_subtype'] == 'mc_dichotomous') {
                    $cells[] = [
                        'row'  => $row,
                        'col'  => $col,
                        'data' => $this->choices[$arrAnswers['value'] - 1],
                    ];
                } elseif ($this->arrData['multiplechoice_subtype'] == 'mc_singleresponse') {
                    $strAnswer = $this->choices[$arrAnswers['value'] - 1];
                    if (($this->arrData['addother']) && ($arrAnswers['value'] == count($this->choices))) {
                        $strAnswer .= ': ' . StringUtil::decodeEntities($arrAnswers['other']);
                    }
                    $cells[] = [
                        'row'  => $row,
                        'col'  => $col,
                        'data' => $strAnswer,
                    ];
                    // Guess a minimum column width.
                } elseif ($this->arrData['multiplechoice_subtype'] == 'mc_multipleresponse') {
                    foreach ($this->choices as $k => $v) {
                        $strAnswer = (is_array($arrAnswers['value']) && array_key_exists($k + 1, $arrAnswers['value'])) ? ($this->arrData['addother'] && ($k + 1 == count($this->choices))) ? StringUtil::decodeEntities($arrAnswers['other']) : 'x' : '';
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
