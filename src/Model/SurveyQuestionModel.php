<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\SurveyBundle\Model;


use Contao\Model;
use Contao\System;

/**
 * Class SurveyPageModel
 *
 * @package HeimrichHannot\SurveyBundle\Model
 */
class SurveyQuestionModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_survey_question';


    /**
     * returns the question and survey page title and pid as array
     *
     * @param $id
     *
     * @return array|null
     */
    public function findSurveyPageTitleAndQuestionById($id)
    {
        $framework = System::getContainer()->get('contao.framework');
        /** @var SurveyQuestionModel $questionModel */
        $questionModel = $framework->getAdapter(static::class)->findByPk($id);

        if (null === $questionModel) {
            return null;
        }

        $result    = $questionModel->row();
        $pageModel = $framework->getAdapter(SurveyPageModel::class)->findByPk($questionModel->pid);

        if (null !== $pageModel) {
            $result['pagetitle'] = $pageModel->title;
            $result['parentID']  = $pageModel->pid;
        }

        return $result;
    }
}