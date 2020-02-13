<?php

$found   = (strlen(\Input::get('id'))) ? \HeimrichHannot\SurveyBundle\Model\SurveyResultModel::findByPid(\Input::get('id')) : null;
$hasData = (null !== $found && 0 < $found->count()) ? true : false;

if ($hasData) {
    /**
     * Table tl_survey_question
     */
    $GLOBALS['TL_DCA']['tl_survey_page'] = [

        // Config
        'config' => [
            'dataContainer' => 'Table',
            'ptable'        => 'tl_survey',
            'ctable'        => ['tl_survey_question'],
            'notEditable'   => true,
            'closed'        => true,
            'sql'           => [
                'keys' => [
                    'id'  => 'primary',
                    'pid' => 'index',
                ],
            ],
        ],
    ];
} else {
    /**
     * Table tl_survey_question
     */
    $GLOBALS['TL_DCA']['tl_survey_page'] = [
        // Config
        'config' => [
            'dataContainer'    => 'Table',
            'ptable'           => 'tl_survey',
            'ctable'           => ['tl_survey_question'],
            'switchToEdit'     => true,
            'enableVersioning' => true,
            'sql'              => [
                'keys' => [
                    'id'  => 'primary',
                    'pid' => 'index',
                ],
            ],
        ],
    ];
}

// List
$GLOBALS['TL_DCA']['tl_survey_page']['list'] = [
    'sorting'    => [
        'mode'                  => 4,
        'filter'                => true,
        'fields'                => ['sorting'],
        'panelLayout'           => 'search,filter,limit',
        'headerFields'          => ['title', 'tstamp', 'description'],
        'child_record_callback' => ['HeimrichHannot\SurveyBundle\Backend\SurveyPagePreview', 'compilePreview'],
    ],
    'operations' => [
        'edit'   => [
            'label'           => &$GLOBALS['TL_LANG']['tl_survey_page']['edit'],
            'href'            => 'table=tl_survey_question',
            'icon'            => 'edit.gif',
//            'button_callback' => ['tl_survey_page', 'editPage'],
        ],
        'copy'   => [
            'label'           => &$GLOBALS['TL_LANG']['tl_survey_page']['copy'],
            'href'            => 'act=paste&mode=copy',
            'icon'            => 'copy.gif',
            'button_callback' => ['tl_survey_page', 'copyPage'],
        ],
        'cut'    => [
            'label'           => &$GLOBALS['TL_LANG']['tl_survey_page']['cut'],
            'href'            => 'act=paste&mode=cut',
            'icon'            => 'cut.gif',
            'attributes'      => 'onclick="Backend.getScrollOffset();"',
            'button_callback' => ['tl_survey_page', 'cutPage'],
        ],
        'delete' => [
            'label'           => &$GLOBALS['TL_LANG']['tl_survey_page']['delete'],
            'href'            => 'act=delete',
            'icon'            => 'delete.gif',
            'attributes'      => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            'button_callback' => ['tl_survey_page', 'deletePage'],
        ],
        'show'   => [
            'label' => &$GLOBALS['TL_LANG']['tl_survey_page']['show'],
            'href'  => 'act=show',
            'icon'  => 'show.gif',
        ],
    ],
];

// Palettes
$GLOBALS['TL_DCA']['tl_survey_page']['palettes'] = [
    'default' => '{title_legend},title,description;{intro_legend},introduction;{template_legend},page_template',
];

// Fields
$GLOBALS['TL_DCA']['tl_survey_page']['fields'] = [
    'id'            => [
        'sql' => "int(10) unsigned NOT NULL auto_increment",
    ],
    'tstamp'        => [
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'pid'           => [
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'sorting'       => [
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'title'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_survey_page']['title'],
        'search'    => true,
        'sorting'   => true,
        'filter'    => true,
        'flag'      => 1,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'maxlength' => 255, 'insertTag' => true],
        'sql'       => "varchar(255) NOT NULL default ''",
    ],
    'description'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_survey_page']['description'],
        'search'    => true,
        'inputType' => 'textarea',
        'eval'      => ['allowHtml' => true, 'style' => 'height:80px;'],
        'sql'       => "text NULL",
    ],
    'language'      => [
        'sql' => "varchar(32) NOT NULL default ''",
    ],
    'introduction'  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_survey_page']['introduction'],
        'default'   => '',
        'search'    => true,
        'inputType' => 'textarea',
        'eval'      => ['allowHtml' => true, 'style' => 'height:80px;', 'rte' => 'tinyMCE'],
        'sql'       => "text NOT NULL",
    ],
    'page_template' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_survey_page']['page_template'],
        'default'          => 'survey_questionblock',
        'inputType'        => 'select',
        'options_callback' => ['tl_survey_page', 'getSurveyTemplates'],
        'eval'             => ['tl_class' => 'w50'],
        'sql'              => "varchar(255) NOT NULL default 'survey_questionblock'",
    ],
    'pagetype'      => [
        'sql' => "varchar(30) NOT NULL default 'standard'",
    ],
];


/**
 * Class tl_survey_page
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_survey_page extends Backend
{
    protected $hasData = null;

    /**
     * Return all survey templates as array
     *
     * @param object
     *
     * @return array
     */
    public function getSurveyTemplates(DataContainer $dc)
    {
        return $this->getTemplateGroup('survey_');
    }

    protected function hasData()
    {
        if (is_null($this->hasData)) {
            /** @var \Model\Collection $result */
            $result = \Contao\System::getContainer()->get('contao.framework')->getAdapter(\HeimrichHannot\SurveyBundle\Model\SurveyResultModel::class)->findBy('pid', \Contao\System::getContainer()->get('huh.request')->get('id'));

            if (null === $result) {
                $this->hasData = false;
            } else {
                $this->hasData = $result->count() > 0;
            }
        }

        return $this->hasData;
    }

    /**
     * Return the edit page button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function editPage($row, $href, $label, $title, $icon, $attributes)
    {
        if ($this->hasData()) {
            return \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        } else {
            return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml($icon, $label) . '</a> ';
        }
    }

    /**
     * Return the copy page button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function copyPage($row, $href, $label, $title, $icon, $attributes)
    {
        if ($this->hasData()) {
            return \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        } else {
            return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml($icon, $label) . '</a> ';
        }
    }

    /**
     * Return the cut page button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function cutPage($row, $href, $label, $title, $icon, $attributes)
    {
        if ($this->hasData()) {
            return \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        } else {
            return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml($icon, $label) . '</a> ';
        }
    }

    /**
     * Return the delete page button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function deletePage($row, $href, $label, $title, $icon, $attributes)
    {
        if ($this->hasData()) {
            return \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        } else {
            return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Contao\System::getContainer()->get('contao.framework')->getAdapter(\Contao\Image::class)->getHtml($icon, $label) . '</a> ';
        }
    }
}

