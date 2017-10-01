<?php
namespace App\Forms\User;

use App\Controllers\ControllerBase;
use App\Controllers\UserController;
use App\Forms\BaseAppForm;
use App\Models\System\User;
use App\Utils\LanguageUtils;
use App\Utils\Sys;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;

/**
 * Class ProfileForm
 * @package App\Forms\User
 */
class ProfileForm extends BaseAppForm
{

    public function initialize()
    {
        /** @var User $entity */
        $entity = $this->getEntity();

        //FirstName
        $firstName = new Text('first_name', [
            'placeholder' => UserController::translate('first_name_placeholder'),
            'class' => 'form-control',
            'required' => true,
            'autofocus' => true,
            'value' => $entity->getValue('first_name')
        ]);

        $firstName->setLabel(User::translate('first_name'));

        $this->add($firstName);
        //LastName
        $lastName = new Text('last_name', [
            'placeholder' => UserController::translate('last_name_placeholder'),
            'class' => 'form-control',
            'required' => true,
            'value' => $entity->getValue('last_name'),
        ]);
        $lastName->setLabel(User::translate('last_name'));
        $this->add($lastName);

        //Birth date
        $dtObj = new \DateTime();

        $realObj = new \DateTime($entity->getValue('birth_date'));
        if ($realObj->format('Y-m-d') > $dtObj->format('Y-m-d')) {
            $realObj = $dtObj;
        }

        $birthDate = new Text('birth_date', [
            'class' => 'datepicker',
            'id' => 'birth_date',
            'value' => $realObj->format('d.m.Y')
        ]);
        $birthDate->setLabel(User::translate('birth_date'));
        $this->add($birthDate);

        //language
        $userLanguage = $entity->getValue(User::FIELD_LANGUAGE);

        $language = new Select(User::FIELD_LANGUAGE, Sys::getTranslatedArrayForSelect(
            LanguageUtils::getAllLanguages(),
            ControllerBase::getTranslatePrefix() . '_'
        ), [
            'id' => 'language',
            'class' => 'form-control',
            'value' => in_array($userLanguage, LanguageUtils::getAllLanguages()) ? $userLanguage :
                LanguageUtils::LANG_RU
        ]);
        $language->setLabel(User::translate(User::FIELD_LANGUAGE));
        $this->add($language);

        //Email
        $email = new Text('email', [
            'placeholder' => 'Email',
            'class' => 'form-control',
            'required' => true,
            'autofocus' => true,
            'readonly' => 'readonly',
            'value' => $entity->getValue('email'),
        ]);

        $email->setLabel('E-Mail');

        $this->add($email);

        //Waves
        $waves = new Text('waves', [
            'placeholder' => 'Кошелек Waves',
            'class' => 'form-control',
            'value' => ''
        ]);

        $waves->setLabel('Кошелек Waves');
        $this->add($waves);

        //email sign for the case of manager or superadmin
        if ($entity->checkRole(User::USER_ROLE_SUPER_ADMIN | User::USER_ROLE_SALES_MANAGER)) {
            $sign = new TextArea(User::FIELD_SETTING_EMAIL_SIGN, [
                'class' => 'form-control',
                'value' => $entity->getSerializedValue(User::FIELD_SETTINGS, User::FIELD_SETTING_EMAIL_SIGN),
                'rows' => 4
            ]);
            $sign->setLabel(User::translate(User::FIELD_SETTINGS . '_' .
                User::FIELD_SETTING_EMAIL_SIGN));
            $this->add($sign);
        }
    }
}

