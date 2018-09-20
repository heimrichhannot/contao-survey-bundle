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

class TanExporter extends ExcelExporter
{
    public function getEntities($pid)
    {
        $surveyId = System::getContainer()->get('huh.request')->get('id');
        $dbResult = $this->framework->createInstance(Database::class)->prepare("SELECT tan, tstamp, used FROM tl_survey_pin_tan WHERE pid = ? ORDER BY tstamp DESC, id DESC")->execute($surveyId);

        return $dbResult;
    }
}