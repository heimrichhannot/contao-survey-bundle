<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\SurveyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use HeimrichHannot\SurveyBundle\DependencyInjection\SurveyExtension;

class HeimrichHannotContaoSurveyBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new SurveyExtension();
    }

    public function getParent()
    {
    }
}