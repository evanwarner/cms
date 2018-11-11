<?php
/**
 * Created by PhpStorm.
 * User: gieltettelaarlaptop
 * Date: 03/11/2018
 * Time: 15:30
 */

namespace craftunit\validators;

use Codeception\Test\Unit;
use craft\test\mockclasses\models\ExampleModel;
use craft\validators\UserPasswordValidator;


/**
 * Class PasswordValidatorTest.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @author Global Network Group | Giel Tettelaar <giel@yellowflash.net>
 * @since  3.0
 */
class PasswordValidatorTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var UserPasswordValidator
     */
    protected $passwordValidator;

    /**
     * @var ExampleModel
     */
    protected $model;

    public function _before()
    {
        $this->passwordValidator = new UserPasswordValidator();
        $this->model = new ExampleModel();
    }

    /**
     * @dataProvider passwordValidationData
     * @param      $inputValue
     * @param bool $mustValidate
     */
    public function testValidation($inputValue, bool $mustValidate, string $currentPass = null)
    {
        $this->model->exampleParam = $inputValue;

        if ($currentPass) {
            $this->passwordValidator->currentPassword = $currentPass;
        }

        $result = $this->passwordValidator->validateAttribute($this->model, 'exampleParam');

        if ($mustValidate) {
            $this->assertArrayNotHasKey('exampleParam', $this->model->getErrors());
        } else {
            $this->assertArrayHasKey('exampleParam', $this->model->getErrors());
        }
    }

    public function passwordValidationData()
    {
        return [
            ['22', false],
            ['123456', true],
            ['!@#$%^&*()', true],
            ['161charsoaudsoidsaiadsjdsapoisajdpodsapaasdjosadojdsaodsapojdaposjosdakshjdsahksakhjhsadskajaskjhsadkdsakdsjhadsahkksadhdaskldskldslkdaslkadslkdsalkdsalkdsalkdsa', false]
        ];
    }

    /**
     * @dataProvider customConfigData
     * @param $input
     * @param $mustvalidate
     */
    public function testCustomConfig($input, $mustValidate, $min, $max)
    {
        $passVal = new UserPasswordValidator(['min' => $min, 'max' => $max]);
        $this->model->exampleParam = $input;
        $passVal->validateAttribute($this->model, 'exampleParam');

        if ($mustValidate) {
            $this->assertArrayNotHasKey('exampleParam', $this->model->getErrors());
        } else {
            $this->assertArrayHasKey('exampleParam', $this->model->getErrors());
        }

    }
    public function customConfigData()
    {
        return [
            ['password', false, 0, 0],
            ['3', false, 2, 0],
            ['123', true, 3, 3],
            ['123', true, 2, 3],
            ['123', true, 3, 4],
            ['', true, -1, 0],
            [null, false, -1, 0],
        ];
    }

    /**
     * @dataProvider forceDiffValidation
     * @param $mustValidate
     * @param $input
     * @param $currentPassword
     */
    public function testForceDiffValidation($mustValidate, $input, $currentPassword)
    {
        $this->passwordValidator->forceDifferent = true;
        $this->passwordValidator->currentPassword = \Craft::$app->getSecurity()->hashPassword($currentPassword);
        $this->model->exampleParam = $input;
        $this->passwordValidator->validateAttribute($this->model, 'exampleParam');

        if ($mustValidate) {
            $this->assertArrayNotHasKey('exampleParam', $this->model->getErrors());
        } else {
            $this->assertArrayHasKey('exampleParam', $this->model->getErrors());
        }
    }
    public function forceDiffValidation()
    {
        return [
            [false, 'test', 'test'],
            [false, '', ''],
            // Not 6 chars
            [false, 'test', 'difftest'],
            [true, 'onetwothreefourfivesix', 'onetwothreefourfivesixseven'],
            // Spaces?
            [true, '      ', '         '],

        ];
    }

    /**
     * @dataProvider isEmptyData
     * @param $result
     * @param $input
     */
    public function testIsEmpty($result, $input)
    {

    }
    public function isEmptyData()
    {
        return [

        ];
    }

}
