<?php

namespace HeimrichHannot\SurveyBundle\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\SurveyBundle\Manager\SurveyManager;
use HeimrichHannot\SurveyBundle\Model\SurveyModel;
use HeimrichHannot\SurveyBundle\Model\SurveyPageModel;
use HeimrichHannot\SurveyBundle\Model\SurveyParticipantModel;
use HeimrichHannot\SurveyBundle\Model\SurveyPinTanModel;
use HeimrichHannot\SurveyBundle\Model\SurveyQuestionModel;
use HeimrichHannot\SurveyBundle\Model\SurveyResultModel;
use Model\Collection;

/**
 * Class ContentSurvey
 *
 * @package HeimrichHannot\SurveyBundle\ContentElement
 */
class ContentSurvey extends ContentElement
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_survey';

    /**
     * @var null|SurveyModel
     */
    protected $surveyModel = null;

    /**
     * @var string
     */
    protected $questionblock_template = 'survey_questionblock';

    /**
     * @var
     */
    protected $pin;

    /**
     * @var
     */
    private $questionpositions;

    /**
     * @var SurveyManager
     */
    protected $surveyManager;


    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var \HeimrichHannot\RequestBundle\Component\HttpFoundation\Request|object
     */
    protected $request;

    public function __construct($objElement, $strColumn = 'main')
    {
        $this->surveyManager = System::getContainer()->get('huh.survey.manager');
        $this->framework     = System::getContainer()->get('contao.framework');
        $this->request       = System::getContainer()->get('huh.request');

        parent::__construct($objElement, $strColumn);
    }

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate           = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### SURVEY ###';

            return $objTemplate->parse();
        }

        $this->strTemplate = (strlen($this->surveyTpl)) ? $this->surveyTpl : $this->strTemplate;

        return parent::generate();
    }

    /**
     * Create an array of widgets containing the questions on a given survey page
     *
     * @param array
     * @param boolean
     */
    protected function createSurveyPage($pagerow, $pagenumber, $validate = true, $goback = false)
    {
        $this->questionpositions = [];
        if (!strlen($this->pin)) {
            $this->pin = $this->request->getPost('pin');
        }
        $surveyPage          = [];
        $pageQuestionCounter = 1;
        $doNotSubmit         = false;

        $questions = $this->framework->getAdapter(SurveyQuestionModel::class)->findBy('pid', $pagerow['id'], ['order' => 'sorting']);

        if (null === $questions) {
            return [];
        }

        /** @var Model $question */
        foreach ($questions as $question) {
            $strClass = $GLOBALS['TL_SVY'][$question->questiontype];
            // Continue if the class is not defined
            if (!class_exists($strClass)) {
                continue;
            }

            /** @var Widget $widget */
            $widget                     = new $strClass();
            $widget->surveydata         = $question->row();
            $widget->absoluteNumber     = $this->getQuestionPosition($question->id, $this->surveyModel->id);
            $widget->pageQuestionNumber = $pageQuestionCounter;
            $widget->pageNumber         = $pagenumber;
            $widget->cssClass           = ($question->cssClass != '' ? ' ' . $question->cssClass : '') . ($widget->absoluteNumber % 2 == 0 ? ' odd' : ' even');
            array_push($surveyPage, $widget);
            $pageQuestionCounter++;

            if ($validate) {
                $widget->validate();
                if ($widget->hasErrors()) {
                    $doNotSubmit = true;
                }
            } else {
                // load existing values
                switch ($this->surveyModel->access) {
                    case 'anon':
                    case 'anoncode':
                        /** @var Collection $surveyResult */
                        $surveyResult = $this->framework->getAdapter(SurveyResultModel::class)->findBy(['pid=?', 'qid=?', 'pin=?'], [$this->surveyModel->id, $widget->id, $this->pin]);
                        break;
                    case 'nonanoncode':
                        $surveyResult = $this->framework->getAdapter(SurveyResultModel::class)->findBy(['pid=?', 'qid=?', 'uid=?'], [$this->surveyModel->id, $widget->id, $this->User->id]);
                        break;
                }
                if (null !== $surveyResult) {
                    $widget->value = StringUtil::deserialize($surveyResult->fetchAll());
                }
            }
        }

        if ($validate) {
            // HOOK: pass validated questions to callback functions
            if (isset($GLOBALS['TL_HOOKS']['surveyQuestionsValidated']) && is_array($GLOBALS['TL_HOOKS']['surveyQuestionsValidated'])) {
                foreach ($GLOBALS['TL_HOOKS']['surveyQuestionsValidated'] as $callback) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($surveyPage, $pagerow);
                }
            }
        } else {
            // HOOK: pass loaded questions to callback functions
            if (isset($GLOBALS['TL_HOOKS']['surveyQuestionsLoaded']) && is_array($GLOBALS['TL_HOOKS']['surveyQuestionsLoaded'])) {
                foreach ($GLOBALS['TL_HOOKS']['surveyQuestionsLoaded'] as $callback) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($surveyPage, $pagerow);
                }
            }
        }

        if ($validate && $this->request->getPost('FORM_SUBMIT') == 'tl_survey' && !strlen($this->pin)) {
            if ($this->surveyModel->usecookie && strlen($_COOKIE['TLsvy_' . $this->surveyModel->id])) {
                // restore lost PIN from cookie
                $this->pin = $_COOKIE['TLsvy_' . $this->surveyModel->id];
            } else {
                // PIN got lost, restart

                /** @var PageModel $objPage */
                global $objPage;
                $this->redirect($objPage->getFrontendUrl());
            }
        }

        // save survey values
        if ($validate && $this->request->getPost('FORM_SUBMIT') == 'tl_survey' && (!$doNotSubmit || $goback)) {
            if (!strlen($this->pin) || !$this->isValid($this->pin)) {
                global $objPage;
                $this->redirect($objPage->getFrontendUrl());
            }
            foreach ($surveyPage as $question) {
                switch ($this->surveyModel->access) {
                    case 'anon':
                    case 'anoncode':
                        $this->surveyManager->deleteByClass(SurveyResultModel::class, [' pid=?', ' qid=?', ' pin=?'], [$this->surveyModel->id, $question->id, $this->pin]);
                        $value = $question->value;
                        if (is_array($question->value)) {
                            $value = serialize($question->value);
                        }
                        if (strlen($value)) {
                            $this->surveyManager->createNewModelByClass(SurveyResultModel::class, ['tstamp' => time(), 'pid' => $this->surveyModel->id, 'qid' => $question->id, 'pin' => $this->pin, 'result' => $value]);
                        }
                        break;
                    case 'nonanoncode':
                        $this->surveyManager->deleteByClass(SurveyResultModel::class, [' pid=?', ' qid=?', ' uid=?'], [$this->surveyModel->id, $question->id, $this->User->id]);
                        $value = $question->value;
                        if (is_array($question->value)) {
                            $value = serialize($question->value);
                        }
                        if (strlen($value)) {
                            $this->surveyManager->createNewModelByClass(SurveyResultModel::class, ['tstamp' => time(), 'pid' => $this->surveyModel->id, 'qid' => $question->id, 'pin' => $this->pin, 'uid' => $this->User->id, 'result' => $value]);
                        }
                        break;
                }
            }
            if ($this->request->getPost('finish')) {
                // finish the survey
                switch ($this->surveyModel->access) {
                    case 'anon':
                    case 'anoncode':
                        $this->surveyManager->updateModelByClass(SurveyParticipantModel::class, ['finished' => '1'], ['pid=?', 'pin=?'], [$this->surveyModel->id, $this->pin]);
                        break;
                    case 'nonanoncode':
                        $this->surveyManager->updateModelByClass(SurveyParticipantModel::class, ['finished' => '1'], ['pid=?', 'uid=?'], [$this->surveyModel->id, $this->User->id]);
                        break;
                }
                // HOOK: pass survey data to callback functions when survey is finished
                if (isset($GLOBALS['TL_HOOKS']['surveyFinished']) && is_array($GLOBALS['TL_HOOKS']['surveyFinished'])) {
                    foreach ($GLOBALS['TL_HOOKS']['surveyFinished'] as $callback) {
                        $this->import($callback[0]);
                        $this->$callback[0]->$callback[1]($this->surveyModel->row());
                    }
                }
                if ($this->surveyModel->jumpto) {
                    /** @var PageModel $pageModel */
                    $pageModel = $this->framework->getAdapter(PageModel::class)->findByPk($this->surveyModel->jumpto);
                    if (null !== $pageModel) {
                        $this->redirect($pageModel->getFrontendUrl());
                    }
                }
            }
        }

        return (($doNotSubmit || !$validate) && !$goback) ? $surveyPage : [];
    }

    protected function getQuestionPosition($question_id, $survey_id)
    {
        if ($question_id > 0 && $survey_id > 0) {
            if (!count($this->questionpositions)) {
                $execute                 = (method_exists($this->Database, 'executeUncached')) ? 'executeUncached' : 'execute';
                $this->questionpositions = $this->Database->prepare("SELECT tl_survey_question.id FROM tl_survey_question, tl_survey_page WHERE tl_survey_question.pid = tl_survey_page.id AND tl_survey_page.pid = ? ORDER BY tl_survey_page.sorting, tl_survey_question.sorting")->$execute($survey_id)->fetchEach('id');
            }

            return array_search($question_id, $this->questionpositions) + 1;
        } else {
            return 0;
        }
    }

    /**
     * Check if the active participant is still valid (maybe participant data was deleted by the survey administrator)
     *
     * @return boolean
     **/
    protected function isValid($pin)
    {
        if (strlen($pin) == 0) {
            return false;
        }
        $participants = $this->framework->getAdapter(SurveyParticipantModel::class)->findBy(['pin=?', 'pid=?'], [$pin, $this->surveyModel->id]);

        if (null === $participants) {
            return false;
        }

        if (1 === $participants->count()) {
            return true;
        }

        return false;
    }

    protected function outIntroductionPage()
    {
        switch ($this->surveyModel->access) {
            case 'anon':
                $status = "";
                if ($this->surveyModel->usecookie) {
                    $status = $this->surveyManager->getSurveyStatus($this->surveyModel->id, $_COOKIE['TLsvy_' . $this->surveyModel->id]);
                }
                if (strcmp($status, "finished") == 0) {
                    $this->Template->errorMsg         = $GLOBALS['TL_LANG']['ERR']['survey_already_finished'];
                    $this->Template->hideStartButtons = true;
                }
                break;
            case 'anoncode':
                $this->loadLanguageFile("tl_content");
                $this->Template->needsTAN        = true;
                $this->Template->txtTANInputDesc = $GLOBALS['TL_LANG']['tl_content']['enter_tan_to_start_desc'];
                $this->Template->txtTANInput     = $GLOBALS['TL_LANG']['tl_content']['enter_tan_to_start'];
                if (strlen($this->request->get('code'))) {
                    $this->Template->tancode = $this->request->get('code');
                }
                break;
            case 'nonanoncode':
                if (!$this->User->id) {
                    $this->Template->errorMsg         = $GLOBALS['TL_LANG']['ERR']['survey_no_member'];
                    $this->Template->hideStartButtons = true;
                } elseif ($this->surveyModel->limit_groups) {
                    if (!$this->surveyManager->isUserAllowedToTakeSurvey($this->surveyModel)) {
                        $this->Template->errorMsg         = $GLOBALS['TL_LANG']['ERR']['survey_no_allowed_member'];
                        $this->Template->hideStartButtons = true;
                    }
                } else {
                    $status = $this->surveyManager->getSurveyStatusForMember($this->surveyModel->id, $this->User->id);
                    if (strcmp($status, "finished") == 0) {
                        $this->Template->errorMsg         = $GLOBALS['TL_LANG']['ERR']['survey_already_finished'];
                        $this->Template->hideStartButtons = true;
                    }
                }
                break;
        }
    }

    /**
     * Insert a new participant dataset
     */
    protected function insertParticipant($pid, $pin, $uid = 0)
    {
        $newParticipant         = new SurveyParticipantModel();
        $newParticipant->tstamp = time();
        $newParticipant->pid    = $pid;
        $newParticipant->pin    = $pin;
        $newParticipant->uid    = $uid;
        $newParticipant->save();
    }

    /**
     * Insert a new participant dataset
     */
    protected function insertPinTan($pid, $pin, $tan, $used)
    {
        $newParticipant         = new SurveyPinTanModel();
        $newParticipant->tstamp = time();
        $newParticipant->pid    = $pid;
        $newParticipant->pin    = $pin;
        $newParticipant->tan    = $tan;
        $newParticipant->used   = $used;
        $newParticipant->save();
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        if (System::getContainer()->get('huh.utils.container')->isFrontend() && !BE_USER_LOGGED_IN && ($this->invisible || ($this->start > 0 && $this->start > time()) || ($this->stop > 0 && $this->stop < time()))) {
            return '';
        }

        // Get front end user object
        $this->import('FrontendUser', 'User');
        $surveyId          = (strlen($this->request->getPost('survey'))) ? $this->request->getPost('survey') : $this->survey;
        $this->surveyModel = $this->framework->getAdapter(SurveyModel::class)->findByPk($surveyId);

        if (null === $this->surveyModel) {
            return;
        }

        // check date activation
        if ((strlen($this->surveyModel->online_start)) && ($this->surveyModel->online_start > time())) {
            $this->Template->protected = true;

            return;
        }
        if ((strlen($this->surveyModel->online_end)) && ($this->surveyModel->online_end < time())) {
            $this->Template->protected = true;

            return;
        }

        /** @var Collection $pages */
        $pages = $this->framework->getAdapter(SurveyPageModel::class)->findBy('pid', $surveyId, ['order' => 'sorting']);

        if (null === $pages) {
            $pages = [];
        } else {
            $pages = $pages->fetchAll();
        }

        $page = ($this->request->getPost('page')) ? $this->request->getPost('page') : 0;
        // introduction page / status
        if ($page == 0) {
            $this->outIntroductionPage();
        }

        // check survey start
        if ($this->request->getPost('start') || ($this->surveyModel->immediate_start == 1 && !$this->request->getPost('FORM_SUBMIT'))) {
            $page = 0;
            switch ($this->surveyModel->access) {
                case 'anon':
                    if (($this->surveyModel->usecookie) && strlen($_COOKIE['TLsvy_' . $this->surveyModel->id]) && $this->surveyManager->checkPINTAN($this->surveyModel->id, $_COOKIE['TLsvy_' . $this->surveyModel->id]) !== false) {
                        $page      = $this->surveyManager->getLastPageForPIN($this->surveyModel->id, $_COOKIE['TLsvy_' . $this->surveyModel->id]);
                        $this->pin = $_COOKIE['TLsvy_' . $this->surveyModel->id];
                    } else {
                        $pintan = $this->surveyManager->generatePIN_TAN();
                        if ($this->surveyModel->usecookie) {
                            setcookie('TLsvy_' . $this->surveyModel->id, $pintan["PIN"], time() + 3600 * 24 * 365, "/");
                        }
                        $this->pin = $pintan["PIN"];
                        // add pin/tan
                        $this->insertPinTan($this->surveyModel->id, $pintan["PIN"], $pintan["TAN"], 1);
                        $this->insertParticipant($this->surveyModel->id, $pintan["PIN"]);
                        $page = 1;
                    }
                    break;
                case 'anoncode':
                    $tan = $this->request->getPost('tan');
                    if ((strcmp($this->request->getPost('FORM_SUBMIT'), 'tl_survey_form') == 0) && (strlen($tan))) {
                        $result = $this->surveyManager->checkPINTAN($this->surveyModel->id, "", $tan);
                        if ($result === false) {
                            $this->Template->tanMsg = $GLOBALS['TL_LANG']['ERR']['survey_wrong_tan'];
                        } else {
                            $this->pin = $this->surveyManager->getPINforTAN($this->surveyModel->id, $tan);
                            if ($result == 0) {
                                // activate the TAN
                                $this->surveyManager->updateModelByClass(SurveyPinTanModel::class, ['used' => 1], ['tan=?', 'pid=?'], [$tan, $this->surveyModel->id]);
                                // set pin
                                if ($this->surveyModel->usecookie) {
                                    setcookie('TLsvy_' . $this->surveyModel->id, $this->pin, time() + 3600 * 24 * 365, "/");
                                }
                                $this->insertParticipant($this->surveyModel->id, $this->pin);
                                $page = 1;
                            } else {
                                $status = $this->surveyManager->getSurveyStatus($this->surveyModel->id, $this->pin);
                                if (strcmp($status, "finished") == 0) {
                                    $this->Template->errorMsg         = $GLOBALS['TL_LANG']['ERR']['survey_already_finished'];
                                    $this->Template->hideStartButtons = true;
                                } else {
                                    $page = $this->surveyManager->getLastPageForPIN($this->surveyModel->id, $this->pin);
                                }
                            }
                        }
                    } else {
                        $this->Template->tanMsg = $GLOBALS['TL_LANG']['ERR']['survey_please_enter_tan'];
                    }
                    break;
                case 'nonanoncode':
                    $participant = $this->framework->getAdapter(SurveyParticipantModel::class)->findBy(['pid=?', 'uid=?'], [$this->surveyModel->id, $this->User->id]);
                    if (null === $participant) {
                        $page = 1;
                        break;
                    }
                    if (!$participant->uid) {
                        $pintan    = $this->surveyManager->generatePIN_TAN();
                        $this->pin = $pintan["PIN"];
                        $this->insertParticipant($this->surveyModel->id, $pintan["PIN"], $this->User->id);
                    } else {
                        $this->pin = $participant->pin;
                    }
                    $page = strlen($participant->lastpage) ? $participant->lastpage : 1;
                    break;
            }
        }

        // check question input and save input or return a question list of the page
        $surveypage = [];
        if (($page > 0 && $page <= count($pages))) {
            if ($this->request->getPost('FORM_SUBMIT') == 'tl_survey') {
                $goback     = (strlen($this->request->getPost("prev"))) ? true : false;
                $surveypage = $this->createSurveyPage($pages[$page - 1], $page, true, $goback);
            }
        }

        // submit successful, calculate next page and return a question list of the new page
        if (count($surveypage) == 0) {
            if (strlen($this->request->getPost("next"))) {
                $page++;
            }
            if (strlen($this->request->getPost("finish"))) {
                $page++;
            }
            if (strlen($this->request->getPost("prev"))) {
                $page--;
            }

            $surveypage = $this->createSurveyPage($pages[$page - 1], $page, false);
        }

        // save position of last page (for resume)
        if ($page > 0) {
            $this->surveyManager->updateModelByClass(SurveyParticipantModel::class, ['lastpage' => $page], ['pid=?', ' pin=?'], [$this->surveyModel->id, $this->pin]);
            if (strlen($pages[$page - 1]['page_template'])) {
                $this->questionblock_template = $pages[$page - 1]['page_template'];
            }
        }

        $questionBlockTemplate             = new FrontendTemplate($this->questionblock_template);
        $questionBlockTemplate->surveypage = $surveypage;

        // template output
        $this->Template->pages       = $pages;
        $this->Template->survey_id   = $this->surveyModel->id;
        $this->Template->show_title  = $this->surveyModel->show_title;
        $this->Template->show_cancel = ($page > 0 && count($surveypage)) ? $this->surveyModel->show_cancel : false;
        $this->Template->surveytitle = StringUtil::specialchars($this->surveyModel->title);
        $this->Template->cancel      = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['cancel_survey']);

        /** @var PageModel $objPage */
        global $objPage;
        $this->Template->cancellink      = $objPage->getFrontendUrl();
        $this->Template->allowback       = $this->surveyModel->allowback;
        $this->Template->questionblock   = $questionBlockTemplate->parse();
        $this->Template->page            = $page;
        $this->Template->introduction    = $this->surveyModel->introduction;
        $this->Template->finalsubmission = ($this->surveyModel->finalsubmission) ? $this->surveyModel->finalsubmission : $GLOBALS['TL_LANG']['MSC']['survey_finalsubmission'];

        $this->Template->pageXofY = $GLOBALS['TL_LANG']['MSC']['page_x_of_y'];
        $this->Template->next     = $GLOBALS['TL_LANG']['MSC']['survey_next'];
        $this->Template->prev     = $GLOBALS['TL_LANG']['MSC']['survey_prev'];
        $this->Template->start    = $GLOBALS['TL_LANG']['MSC']['survey_start'];
        $this->Template->finish   = $GLOBALS['TL_LANG']['MSC']['survey_finish'];
        $this->Template->pin      = $this->pin;
        $this->Template->action   = ampersand(Environment::get('request'));
    }
}