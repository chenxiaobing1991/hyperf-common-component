<?php
declare(strict_types=1);
namespace Cxb\Hyperf\Common\Validators;


class DefaultValueValidator extends Validator
{
    public $value;//默认值



    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->isEmpty($model->$attribute)) {
            if ($this->value instanceof \Closure) {
                $model->$attribute = call_user_func($this->value, $model, $attribute);
            } else {
                $model->$attribute = $this->value;
            }
        }
    }
}
