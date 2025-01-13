<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;

/**
 * 内置函数
 * Class InlineValidator
 * @package common\Validators
 */
class InlineValidator extends  Validator
{
    public $method;//方法名
    public $params;//自定义参数
    /**
     * 校验规则
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $method = $this->method;
        if (is_string($method)) {
            $method = [$model, $method];
        }
        call_user_func($method, $attribute, $this->params);
    }
}