<?php

namespace HeimrichHannot\SurveyBundle\Backend;

use Contao\Backend;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\SurveyBundle\Model\SurveyPageModel;
use Model\Collection;


/**
 * Class SurveyPagePreview
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyPagePreview extends Backend
{
    /**
     * @param array $row
     *
     * @return string
     */
    public function compilePreview(array $row)
    {
        /** @var Collection $surveyPageCollection */
        $surveyPageCollection = System::getContainer()->get('contao.framework')->getAdapter(SurveyPageModel::class)->findBy(['pid=?', 'sorting<?'], [$row["pid"], $row["sorting"]]);
        $position             = 1;

        if (null !== $surveyPageCollection) {
            $position = $surveyPageCollection->count() + 1;
        }

        $template              = new FrontendTemplate('be_survey_page_preview');
        $template->page        = $GLOBALS['TL_LANG']['tl_survey_page']['page'];
        $template->position    = $position;
        $template->title       = StringUtil::specialchars($row['title']);
        $template->description = StringUtil::specialchars($row['description']);

        return $template->parse();
    }

}

