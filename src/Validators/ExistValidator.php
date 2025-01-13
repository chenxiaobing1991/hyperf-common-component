<?php

declare(strict_types=1);
namespace Cxb\Hyperf\Common\Validators;

/**
 * 判断是否存在
 * Class ExistValidator
 * @package App\Common\Validators
 */
class ExistValidator extends Validator
{
    public $targetClass;
    public $targetAttribute;
    public $filter;
    public $allowArray = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message ="{attribute} is invalid.";
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        if (is_array($targetAttribute)) {
            if ($this->allowArray) {
                throw new \Exception('The "targetAttribute" property must be configured as a string.');
            }
            $params = [];
            foreach ($targetAttribute as $k => $v) {
                $params[$v] = is_int($k) ? $model->$v : $model->$k;
            }
        } else {
            $params = [$targetAttribute => $model->$attribute];
        }

        if (!$this->allowArray) {
            foreach ($params as $value) {
                if (is_array($value)) {
                    $this->addError($model, $attribute,"{$attribute} is invalid.'");

                    return;
                }
            }
        }

        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $query = $this->createQuery($targetClass, $params);//具体的对象
        if (is_array($model->$attribute)) {
            if ($query->count() != count($model->$attribute)) {
                $this->addError($model, $attribute, $this->message);
            }
        } elseif (!$query->exists()) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * 具体的db orm处理
     * @param $targetClass
     * @param $condition
     * @return mixed
     */
    protected function createQuery($targetClass, $condition)
    {

        $query = $targetClass::query()->andWhere($condition);
        if ($this->filter instanceof \Closure) {
            $query=call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->where($this->filter);
        }
        return $query;
    }
}
