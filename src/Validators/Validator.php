<?php
declare(strict_types=1);
namespace Cxb\Hyperf\Common\Validators;

use Cxb\Hyperf\Common\Utils\HyperfUtil;
use Cxb\Hyperf\Common\Component;

/**
 * 规则校验器
 * Class Validator
 * @package common\Db
 */
class Validator
{
    use Component;
    private static $builtInValidators = [
        'boolean' => 'App\Common\Validators\BooleanValidator',
        'default' => 'App\Common\Validators\DefaultValueValidator',
        'exist' => 'App\Common\Validators\ExistValidator',
        'validate_exist' => 'App\Common\Validators\ExistValidator',
        'filter' => 'App\Common\Validators\FilterValidator',
        'in' => 'App\Common\Validators\RangeValidator',
        'integer' => [
            'class' => 'App\Common\Validators\NumberValidator',
            'integerOnly' => true,
        ],
        'number' => 'App\Common\Validators\NumberValidator',
        'required' => 'App\Common\Validators\RequiredValidator',
        'safe' => 'App\Common\Validators\SafeValidator',
        'string' => 'App\Common\Validators\StringValidator',
        'unique' => 'App\Common\Validators\UniqueValidator',
    ];
    public $isEmpty;
    public $attributes=[];//属性注册
    public $message;//错误日志
    public $on=[];//场景

    /**
     * 属性方法校验
     * @param $model
     * @param $attribute
     */
    public function validateAttribute($model,$attribute){
        $result=$this->validateValue($model->{$attribute});
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }
    public function validateAttributes($model, $attributes = null)
    {
        if (is_array($attributes)) {
            $newAttributes = [];
            foreach ($attributes as $attribute) {
                if (in_array($attribute, $this->attributes) || in_array('!' . $attribute, $this->attributes)) {
                    $newAttributes[] = $attribute;
                }
            }
            $attributes = $newAttributes;
        } else {
            $attributes = [];
            foreach ($this->attributes as $attribute) {
                $attributes[] = $attribute[0] === '!' ? substr($attribute, 1) : $attribute;
            }
        }
        foreach ($attributes as $attribute) {
            $this->validateAttribute($model, $attribute);
        }
    }

    /**
     * 实例化校验器
     * @param $type
     * @param $model
     * @param $attributes
     * @param array $params
     */
    final public static function createValidate($type,$model,$attributes,$params=[]){
        $params['attributes'] = $attributes;
        /*  判断是否为闭包函数或内置方法  */
        if($type instanceof \Closure || $model->hasMethod($type)){
            $params['method'] = $type;
            $params['class']=InlineValidator::class;
        }elseif(isset(self::$builtInValidators[$type])){
            $type = static::$builtInValidators[$type];
            if (is_array($type)) {
                $params = array_merge($type, $params);
            } else {
                $params['class'] = $type;
            }

        }else{
            $params['rules']=$type;
            $params['class']=UnlineValidator::class;
        }
        return HyperfUtil::createObject($params);
    }

    /**
     * 重置错误日志
     * @param $model
     * @param $errors
     */
    public function setErrors($model,$errors){
        if(method_exists($model,'setErrors')){
            $model->setErrors($errors);
        }
    }

    /**
     * 添加错误日志
     * @param $model
     * @param $attribute
     * @param $message
     */
    public function  addError($model,$attribute,$message,array $params=[]){
        $params['attribute']=$model->getAttributeLabel($attribute);
        if(method_exists($model,'addError')){
            $model->addError($attribute,$this->getMessage($message,$params));
        }
    }

    /**
     * 设置提示
     * @param $message
     * @param $params
     */
    private function getMessage($message,$params){
        foreach($params as $key=>$value){
            $message=str_replace("{$key}",$value,$message);
        }
        return $message;
    }
    public function isActive($scenario)
    {
        return in_array($scenario, $this->on, true) || empty($this->on);
    }

    /**
     * 判断是否为空
     * @param $value
     * @return bool|mixed
     */
    public function isEmpty($value){
        if ($this->isEmpty !== null) {
            return call_user_func($this->isEmpty, $value);
        } else {
            return $value === null || $value === [] || $value === '';
        }
    }
}