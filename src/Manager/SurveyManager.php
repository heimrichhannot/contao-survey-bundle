<?php

namespace HeimrichHannot\SurveyBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\SurveyBundle\Model\SurveyParticipantModel;
use HeimrichHannot\SurveyBundle\Model\SurveyPinTanModel;

/**
 * Class Survey
 *
 * @package HeimrichHannot\SurveyBundle\Backend
 */
class SurveyManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Import String library
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param $id
     * @param $pin
     *
     * @return null
     */
    public function getTANforPIN($id, $pin)
    {
        $pinTan = $this->framework->getAdapter(SurveyPinTanModel::class)->findOneBy(['pid=?', 'pin=?'], [$id, $pin]);

        if (null === $pinTan) {
            return null;
        }

        return $pinTan->tan;
    }

    /**
     * @param $id
     * @param $tan
     *
     * @return null
     */
    public function getPINforTAN($id, $tan)
    {
        $pinTan = $this->framework->getAdapter(SurveyPinTanModel::class)->findOneBy(['pid=?', 'tan=?'], [$id, $tan]);

        if (null === $pinTan) {
            return null;
        }

        return $pinTan->pin;
    }

    /**
     * @param $id
     * @param $pin
     *
     * @return bool|string
     */
    public function getSurveyStatus($id, $pin)
    {
        $participantModel = $this->framework->getAdapter(SurveyParticipantModel::class)->findOneBy(['pid=?', 'pin=?'], [$id, $pin]);

        if (null === $participantModel) {
            return false;
        }

        if ($participantModel->finished) {
            return "finished";
        } else {
            return "started";
        }
    }

    /**
     * Checks a PIN and returns FALSE if the pin does not exist, 0 if the pin exists but wasn't used and a timestamp if the pin exists and was used
     *
     * @return bool|string
     */
    public function checkPINTAN($id, $pin = "", $tan = "")
    {
        if (strlen($pin)) {
            $pinTan = $this->framework->getAdapter(SurveyPinTanModel::class)->findOneBy(['pid=?', 'pin=?'], [$id, $pin]);
        } else {
            $pinTan = $this->framework->getAdapter(SurveyPinTanModel::class)->findOneBy(['pid=?', 'tan=?'], [$id, $tan]);
        }
        if (null !== $pinTan) {
            return $pinTan->used;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @param $uid
     *
     * @return bool|string
     */
    public function getSurveyStatusForMember($id, $uid)
    {
        $participantModel = $this->framework->getAdapter(SurveyParticipantModel::class)->findOneBy(['pid=?', 'uid=?'], [$id, $uid]);

        if (null === $participantModel) {
            return false;
        }

        if ($participantModel->finished) {
            return "finished";
        } else {
            return "started";
        }
    }

    /**
     * @param        $length
     * @param string $type
     *
     * @return string
     */
    protected function generateCode($length, $type = 'alphanum')
    {
        $codeString = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        switch ($type) {
            case 'alpha':
                $codeString = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                break;
            case 'num':
                $codeString = "0123456789";
                break;
        }
        mt_srand();
        $code = "";
        for ($i = 1; $i <= $length; $i++) {
            $index = mt_rand(0, strlen($codeString) - 1);
            $code  .= substr($codeString, $index, 1);
        }

        return $code;
    }

    /**
     * @param $objSurvey
     *
     * @return bool
     */
    public function isUserAllowedToTakeSurvey(&$objSurvey)
    {
        $groups = (!strlen($objSurvey->allowed_groups)) ? [] : StringUtil::deserialize($objSurvey->allowed_groups, true);
        if (count($groups) == 0) {
            return false;
        }
        $frontendUser = $this->framework->createInstance(FrontendUser::class);
        if (!$frontendUser->id) {
            return false;
        }
        $userGroups = StringUtil::deserialize($frontendUser->groups, true);
        if (count(array_intersect($userGroups, $groups))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @param $pin
     *
     * @return null
     */
    public function getLastPageForPIN($id, $pin)
    {
        $participantModel = $this->framework->getAdapter(SurveyParticipantModel::class)->findOneBy(['pid=?', 'pin=?'], [$id, $pin]);

        if (null === $participantModel) {
            return null;
        }

        return $participantModel->lastpage;
    }

    protected function generatePIN()
    {
        return $this->generateCode(6);
    }

    protected function generateTAN()
    {
        return $this->generateCode(6, 'num');
    }

    public function generatePIN_TAN()
    {
        return [
            "PIN" => $this->generatePIN(),
            "TAN" => $this->generateTAN(),
        ];
    }

    /**
     * @param string $class
     * @param array  $keys
     * @param array  $values
     *
     * @return bool|int
     */
    public function deleteByClass(string $class, array $keys, array $values)
    {
        if (!class_exists($class)) {
            return false;
        }
        /** @var Model|null|Model\Collection $model */
        $model = $this->framework->getAdapter($class)->findBy($keys, $values);

        if (null === $model) {
            return false;
        }

        if ($model instanceof Model) {
            return $model->delete();
        }

        if ($model instanceof Model\Collection) {
            foreach ($model as $singleModel) {
                $singleModel->delete();
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return Model|null
     */
    public function createNewModelByClass(string $class, array $data)
    {
        if (!class_exists($class)) {
            return null;
        }

        /** @var Model $newModel */
        $newModel = new $class;

        foreach ($data as $field => $value) {
            $newModel->{$field} = $value;
        }

        return $newModel->save();
    }

    /**
     * @param string $class
     * @param array  $data
     * @param array  $keys
     * @param array  $values
     *
     * @return bool|Model
     */
    public function updateModelByClass(string $class, array $data, array $keys, array $values)
    {
        if (!class_exists($class)) {
            return false;
        }

        /** @var Model|null $model */
        $model = $this->framework->getAdapter($class)->findOneBy($keys, $values);

        if (null === $model) {
            return false;
        }

        foreach ($data as $field => $value) {
            $model->{$field} = $value;
        }

        return $model->save();
    }
}

