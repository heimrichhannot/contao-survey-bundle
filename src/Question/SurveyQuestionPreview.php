<?php

namespace HeimrichHannot\SurveyBundle\Question;

use Contao\Backend;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Contao\System;
use HeimrichHannot\SurveyBundle\Model\SurveyQuestionModel;

/**
 * Class SurveyQuestionPreview
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyQuestionPreview
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Import String library
     */
    public function __construct()
    {
        $this->framework = System::getContainer()->get('contao.framework');
    }

    /**
     * @param array $row
     *
     * @return int
     */
    protected function getQuestionNumber(array $row)
    {
        $pinTan = $this->framework->getAdapter(SurveyQuestionModel::class)->findBy(['pid=?', 'sorting<=?'], [$row["pid"], $row["sorting"]]);

        if (null === $pinTan) {
            return 0;
        }

        return $pinTan->numRows;
    }

    /**
     * Compile format definitions and return them as string
     *
     * @param array
     * @param boolean
     *
     * @return string
     */
    public function compilePreview($row, $blnWriteToFile = false)
    {
        $widget   = "";
        $strClass = $GLOBALS['TL_SVY'][$row['questiontype']];
        if (class_exists($strClass)) {
            $objWidget             = new $strClass();
            $objWidget->surveydata = $row;
            $widget                = $objWidget->generate();
        }

        $template                 = new FrontendTemplate('be_survey_question_preview');
        $template->hidetitle      = $row['hidetitle'];
        $template->help           = specialchars($row['help']);
        $template->questionNumber = $this->getQuestionNumber($row);
        $template->title          = specialchars($row['title']);
        $template->obligatory     = $row['obligatory'];
        $template->question       = $row['question'];
        $return                   = $template->parse();
        $return                   .= $widget;

        return $return;
    }

}

