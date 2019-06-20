<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\SurveyBundle\Exporter;


use Box\Spout\Writer\Style\StyleBuilder;
use Contao\Database;
use Contao\System;
use HeimrichHannot\ContaoExporterBundle\Exporter\Concrete\ExcelExporter;

class ResultDetailsExporter extends ExcelExporter
{
    public function getEntities($pid)
    {
        $surveyId = System::getContainer()->get('huh.request')->get('id');
        $dbResult = $this->framework->createInstance(Database::class)->prepare("
				SELECT   tl_survey_question.*,
				         tl_survey_page.title as pagetitle,
					     tl_survey_page.pid as parentID
				FROM     tl_survey_question, tl_survey_page
				WHERE    tl_survey_question.pid = tl_survey_page.id
				AND      tl_survey_page.pid = ?
				ORDER BY tl_survey_page.sorting, tl_survey_question.sorting")->execute($surveyId);

        return $dbResult;
    }

    /**
     * @param array  $formattedRows
     * @param string $fileDir
     * @param string $fileName
     */
    public function exportToDownload($formattedRows, string $fileDir, string $fileName)
    {
        $writer = $this->getDocumentWriter();

        $writer->openToBrowser($fileName);
        $writer->addRows($formattedRows);

        $writer->close();

        exit();
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
        // TODO: find better way
        // generate default array
        $formattedRows = array_fill(0, 500, array_fill(0, 500, ''));

        System::loadLanguageFile('tl_survey_result');
        System::loadLanguageFile('tl_survey_question');
        $sheet = utf8_decode($GLOBALS['TL_LANG']['tl_survey_result']['detailedResults']);

        $cells = $this->exportTopLeftArea();
        foreach ($cells as $cell) {
            $formattedRows[$cell['row']][$cell['col']] = $cell['data'];
        }
        $rowCounter = 8; // question headers will occupy that many rows
        $colCounter = 0;

        $participants = $this->fetchParticipants(System::getContainer()->get('huh.request')->get('id'));
        $cells        = $this->exportParticipantRowHeaders($sheet, $rowCounter, $colCounter, $participants);
        foreach ($cells as $cell) {
            $formattedRows[$cell['row']][$cell['col']] = $cell['data'];
        }

        // init question counters
        $page_no         = 0;
        $rel_question_no = 0;
        $abs_question_no = 0;
        $last_page_id    = 0;

        while ($databaseResult->next()) {
            $row = $databaseResult->row();
            // increase question numbering counters
            $abs_question_no++;
            $rel_question_no++;
            if ($last_page_id != $row['pid']) {
                // page id has changed, increase page no, reset question no on page
                $page_no++;
                $rel_question_no = 1;
                $last_page_id    = $row['pid'];
            }
            $questionCounters = [
                'page_no'         => $page_no,
                'rel_question_no' => $rel_question_no,
                'abs_question_no' => $abs_question_no,
            ];

            $rowCounter = 0; // reset rowCounter for the question headers

            $class = "HeimrichHannot\SurveyBundle\Question\SurveyQuestion" . ucfirst($row["questiontype"]) . 'Ex';
            if (class_exists($class)) {
                $question       = new $class();
                $question->data = $row;
                $cells          = $question->exportDetailsToExcel($rowCounter, $colCounter, $questionCounters, $participants);
                foreach ($cells as $cell) {
                    $formattedRows[$cell['row']][$cell['col']] = $cell['data'];
                }
            }
        }

        return $formattedRows;
    }

    /**
     * Exports base/identifying information for all participants.
     *
     * Every participant has it's own row with several header columns.
     */
    protected function exportParticipantRowHeaders($sheet, &$rowCounter, &$colCounter, $participants)
    {
        $result = [];
        $row    = $rowCounter;
        foreach ($participants as $key => $participant) {
            $col = $colCounter;
            foreach ($participant as $k => $v) {
                if ($k == 'finished') {
                    continue;
                }
                $cell     = [
                    'sheetname' => $sheet,
                    'row'       => $row,
                    'col'       => $col++,
                    'data'      => $v,
                ];
                $result[] = $cell;
            }
            $row++;
        }
        $rowCounter = $row;
        $colCounter = $col;

        return $result;
    }

    /**
     * Exports some basic information in the unused top left area.
     */
    protected function exportTopLeftArea()
    {
        $result = [];

        $this->createBlankCells($result, 6, 3);
        // Legends for the question headers
        $row      = 0;
        $col      = 4;
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_id'] . ':',
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_nr'] . ':',
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_pg_nr'] . ':',
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_type'] . ':',
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_answered'] . ':',
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_skipped'] . ':',
        ];
        $result[] = [
            'row'  => $row++,
            'col'  => $col,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_title'] . ':',
        ];

        // Legends for the participant headers
        $col      = 0;
        $result[] = [
            'row'  => $row,
            'col'  => $col++,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_id_gen'],
        ];
        $result[] = [
            'row'  => $row,
            'col'  => $col++,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_sort'],
        ];
        $result[] = [
            'row'  => $row,
            'col'  => $col++,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_date'],
        ];
        $result[] = [
            'row'  => $row,
            'col'  => $col++,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_lastpage'],
        ];
        $result[] = [
            'row'  => $row,
            'col'  => $col++,
            'data' => $GLOBALS['TL_LANG']['tl_survey_result']['ex_question_participant'],
        ];

        return $result;
    }

    /**
     * Fetches all participants of the given survey.
     *
     * @param int
     *
     * @return array
     */
    protected function fetchParticipants($surveyID)
    {
        $access         = $this->framework->createInstance(Database::class)->prepare("SELECT access FROM tl_survey WHERE id = ?")->execute($surveyID)->fetchAssoc();
        $objParticipant = $this->framework->createInstance(Database::class)->prepare("
				SELECT    par.*,
				          mem.id        AS mem_id,
				          mem.firstname AS mem_firstname,
						  mem.lastname  AS mem_lastname,
						  mem.email     AS mem_email
				FROM      tl_survey_participant AS par
				LEFT JOIN tl_member             AS mem
				ON        par.uid = mem.id
				WHERE     par.pid = ?
				ORDER BY  par.lastpage DESC, par.finished DESC, par.tstamp DESC")->execute($surveyID);

        $result = [];
        $count  = 0;
        while ($objParticipant->next()) {
            $count++;
            if (strcmp($access['access'], 'nonanoncode') != 0) {
                $pin_uid = $objParticipant->pin;
                $display = $objParticipant->pin;
            } else {
                $pin_uid = $objParticipant->pin;
                $display = $objParticipant->mem_firstname . ' ' . $objParticipant->mem_lastname;
                if (strlen($objParticipant->mem_email)) {
                    $display .= ' <' . $objParticipant->mem_email . '>';
                }
                $display = utf8_decode($display);
            }
            $result[$pin_uid] = [
                'id'       => $objParticipant->id,
                'count'    => $count,
                'date'     => date('Y-m-d H:i:s', $objParticipant->tstamp),
                'lastpage' => $objParticipant->lastpage,
                'finished' => $objParticipant->finished,
                'display'  => $display,
            ];
        }

        return $result;
    }

    /**
     * @param array $result
     * @param       $row
     * @param       $cell
     */
    protected function createBlankCells(array &$result, $row, $cell)
    {
        for ($i = 0; $i <= $row; $i++) {
            for ($x = 0; $x <= $cell; $x++) {
                $result[] = [
                    'row'  => $i,
                    'col'  => $x,
                    'data' => '',
                ];
            }
        }
    }
}