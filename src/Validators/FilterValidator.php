<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;

use InvalidArgumentException;

class FilterValidator extends Validator
{

    public $filter;
    /**
     * @var boolean whether the filter should be skipped if an array input is given.
     * If true and an array input is given, the filter will not be applied.
     */
    public $skipOnArray = false;
    /**
     * @var boolean this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     */
    public $skipOnEmpty = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->filter === null) {
            throw new InvalidArgumentException('The "filter" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!$this->skipOnArray || !is_array($value)) {
            call_user_func($this->filter, $value);
        }
    }
}
