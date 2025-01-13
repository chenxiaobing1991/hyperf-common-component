<?php
declare(strict_types=1);
namespace Cxb\Hyperf\Common;

use Cxb\Hyperf\Common\Validators\Validator;
/**
 * @author  chenhuohuo
 * Class Model
 * @package common\Base
 */
trait Model
{
    use Component;

    /**
     * The name of the default scenario.
     */
    public static $SCENARIO_DEFAULT = 'default';
    /**
     * @event ModelEvent an event raised at the beginning of [[validate()]]. You may set
     * [[ModelEvent::isValid]] to be false to stop the validation.
     */
    public static $EVENT_BEFORE_VALIDATE = 'beforeValidate';
    /**
     * @event Event an event raised at the end of [[validate()]]
     */
    public static $EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * @var array validation errors (attribute name => array of errors)
     */
    private $_errors;
    /**
     * @var  list of validators
     */
    private $_validators;
    /**
     * @var string current scenario
     */
    private $_scenario;

    /**
     * 初始化注册方案
     */
    public function init()
    {
        $this->_scenario = self::$SCENARIO_DEFAULT;
    }

    public function rules()
    {
        return [];
    }


    /**
     * 场景初始化
     * @return array[]
     */
    public function scenarios()
    {
        $scenarios = [static::$SCENARIO_DEFAULT => []];
        foreach ($this->getValidators() as $validator) {
            foreach ($validator->on as $scenario) {
                $scenarios[$scenario] = [];//自定义场景注册
            }
        }
        $names = array_keys($scenarios);//获取所有场景
        foreach ($this->getValidators() as $validator) {
            if (empty($validator->on)) {
                foreach ($names as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            } else {
                foreach ($validator->on as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            }
        }
        /*  遍历获取场景字段   */
        foreach ($scenarios as $scenario => $attributes) {
            if (!empty($attributes)) {
                $scenarios[$scenario] = array_keys($attributes);
            }
        }
        return $scenarios;
    }

    /**
     * 验证器
     * @return |array
     */
    public function getValidators()
    {
        if ($this->_validators === null) {
            $validators =new \ArrayObject();
            foreach ($this->rules() as $rule) {
                if ($rule instanceof Validator) {
                    $validators->append($rule);
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) {
                    $validator = Validator::createValidate($rule[1], $this, $rule[0], array_slice($rule, 2));
                    $validators->append($validator);
                }
            }
            $this->_validators = $validators;
        }
        return $this->_validators;
    }

    /**
     * 数据校验
     * @param null $attributeNames
     * @throws \Exception
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }
        $scenarios = $this->scenarios();
        $scenario=$this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new \Exception("Unknown scenario: $scenario");
        }
        /*  真实校验   */
        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }
        foreach ($this->getValidators() as $validator) {
            if ($validator->isActive($scenario)) {
                $validator->validateAttributes($this, $attributeNames);
            }
        }
        return !$this->hasErrors();
    }

    /**
     * 字段定义
     * @return array
     */
    public function attributeLabels(){
        return [];
    }

    /**
     * 获取某一字段的属性
     * @param $attribute
     * @return mixed
     */
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        return isset($labels[$attribute]) ? $labels[$attribute] : $attribute;
    }

    /**
     * 获取name
     * @return string
     */
    public function formName()
    {
        $reflector = new \ReflectionClass($this);
        return $reflector->getShortName();
    }

    /**
     * 设置注册方案
     * @param $scenario
     */
    public function setScenario($scenario){
        $this->_scenario=$scenario;
    }

    /**
     * 获取注册方案
     * @return string
     */
    public function getScenario(){
        return $this->_scenario===null?self::$SCENARIO_DEFAULT:$this->_scenario;
    }

    /**
     * 获取属性集合
     * @return array
     */
    public function activeAttributes()
    {
        $scenarios = $this->scenarios();
        $scenario=$this->getScenario();
        if (!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = $scenarios[$scenario];
        foreach ($attributes as $i => $attribute) {
            if ($attribute[0] === '!') {
                $attributes[$i] = substr($attribute, 1);
            }
        }
        return $attributes;
    }

    public function safeAttributes()
    {
        $scenarios = $this->scenarios();
        $scenario=$this->getScenario();
        if (!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = [];
        foreach ($scenarios[$scenario] as $attribute) {
            if ($attribute[0] !== '!' && !in_array('!' . $attribute, $scenarios[$scenario])) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * 获取当前类属性
     */
    public function attributes():array{
        $reflectionClass = new \ReflectionClass(static::class);
       return array_column($reflectionClass->getProperties(),'name');
    }

    /**
     * 初始化属性对象
     * @param $data
     * @param null $formName
     */
    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        $data=$scope === '' && !empty($data)?$data:($data[$scope]??[]);
        if ($scope === '' && !empty($data)) {
            $this->setAttributes($data);
            return true;
        } elseif (isset($data[$scope])) {
            $this->setAttributes($data[$scope]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 属性赋值
     * @param $data
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if (is_array($values)) {
            $attributes = $safeOnly?array_flip($this->safeAttributes()):$this->attributes();
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
            unset($this->_errors[$attribute]);
        }
    }

    public function addErrors(array $items)
    {
        foreach ($items as $attribute => $errors) {
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $this->addError($attribute, $error);
                }
            } else {
                $this->addError($attribute, $errors);
            }
        }
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addError($attribute, $error = '')
    {
        $this->_errors[$attribute][] = $error;
    }

    public function getFirstError($attribute)
    {
        return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
    }

    public function getFirstErrors()
    {
        if (empty($this->_errors)) {
            return [];
        } else {
            $errors = [];
            foreach ($this->_errors as $name => $es) {
                if (!empty($es)) {
                    $errors[$name] = reset($es);
                }
            }

            return $errors;
        }
    }
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * 错误日志
     * @return array
     */
    public function getErrors(){
        return $this->_errors;
    }


}