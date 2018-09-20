<?php

namespace HeimrichHannot\SurveyBundle\Backend;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Environment;
use Contao\PageModel;
use Contao\PageTree;
use Contao\StringUtil;
use Contao\System;
use Contao\TextField;
use Contao\Widget;
use HeimrichHannot\ContaoExporterBundle\Exporter\Concrete\ExcelExporter;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\SurveyBundle\Model\SurveyPinTanModel;

/**
 * Class SurveyPINTAN
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyPINTAN extends Backend
{
    /**
     * @var bool
     */
    protected $blnSave = true;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        $this->framework = System::getContainer()->get('contao.framework');
        $this->request   = System::getContainer()->get('huh.request');

        parent::__construct();
    }


    /**
     * @param DataContainer $dc
     *
     * @return string
     */
    public function createTAN(DataContainer $dc)
    {
        if ($this->request->get('key') != 'createtan') {
            return '';
        }

        $this->loadLanguageFile("tl_survey_pin_tan");
        $this->Template = new BackendTemplate('be_survey_create_tan');

        $this->Template->nrOfTAN = $this->getTANWidget();

        $this->Template->hrefBack = ampersand(str_replace('&key=createtan', '', Environment::get('request')));
        $this->Template->goBack   = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->headline = $GLOBALS['TL_LANG']['tl_survey_pin_tan']['createtan'];
        $this->Template->request  = ampersand(Environment::get('request'));
        $this->Template->submit   = specialchars($GLOBALS['TL_LANG']['tl_survey_pin_tan']['create']);

        // Create import form
        if ($this->request->getPost('FORM_SUBMIT') == 'tl_export_survey_pin_tan' && $this->blnSave) {
            $nrOfTAN = $this->Template->nrOfTAN->value;
            for ($i = 0; $i < ceil($nrOfTAN); $i++) {
                $pintan = System::getContainer()->get('huh.survey.manager')->generatePIN_TAN();
                // add pin/tan
                $newPinTanModel         = new SurveyPinTanModel();
                $newPinTanModel->tstamp = time();
                $newPinTanModel->pid    = $this->request->get('id');
                $newPinTanModel->pin    = $pintan["PIN"];
                $newPinTanModel->tan    = $pintan["TAN"];
                $newPinTanModel->save();
            }
            $this->redirect(str_replace('&key=createtan', '', Environment::get('request')));
        }

        return $this->Template->parse();
    }

    /**
     * Return the TAN widget as object
     *
     * @param mixed
     *
     * @return object
     */
    protected function getTANWidget($value = null)
    {
        $widget = new TextField();

        $widget->id        = 'nrOfTAN';
        $widget->name      = 'nrOfTAN';
        $widget->mandatory = true;
        $widget->maxlength = 5;
        $widget->rgxp      = 'digit';
        $widget->nospace   = true;
        $widget->value     = $value;
        $widget->required  = true;

        $widget->label = $GLOBALS['TL_LANG']['tl_survey_pin_tan']['nrOfTAN'][0];

        if ($GLOBALS['TL_CONFIG']['showHelp'] && strlen($GLOBALS['TL_LANG']['tl_survey_pin_tan']['nrOfTAN'][1])) {
            $widget->help = $GLOBALS['TL_LANG']['tl_survey_pin_tan']['nrOfTAN'][1];
        }

        // Validate input
        if ($this->request->getPost('FORM_SUBMIT') == 'tl_export_survey_pin_tan') {
            $widget->validate();

            if ($widget->hasErrors()) {
                $this->blnSave = false;
            }
        }

        return $widget;
    }
}
