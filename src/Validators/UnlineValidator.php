<?php
declare(strict_types=1);
namespace Cxb\Hyperf\Common\Validators;
use Hyperf\Context\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;
/**
 * 处理公共条用函数
 * Class InlineValidator
 * @package common\Validators
 */
class UnlineValidator extends  Validator
{


    public $rules;//规则

    public $message;//描述

    public $targetAttribute;//规则属性
    /**
     * 校验规则
     * @param $model
     * @param $attribute
     */
    public function  validateAttribute($model,$attribute){
        $validate=ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class);
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        $targetAttribute=is_array($targetAttribute)?$targetAttribute:[$targetAttribute];
        $params =$rules= [];
        foreach ($targetAttribute as $k => $v) {
            $k = is_int($k) ? $v : $k;
            $params[$k]=$model->$k;
            $rules[$k]=$this->rules;
        }
        $message=$this->message===null?[]:$this->message;
        $validator=$validate->make($params,$rules,$message);
        if($validator->fails())
            $this->setErrors($model,$validator->errors());//重置日志
    }

    /**
     * 实现校验----暂未实现
     * @param $value
     */
    public function validateValue($value){

    }

}