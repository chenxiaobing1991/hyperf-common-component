<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;
use InvalidArgumentException;
class RangeValidator extends Validator
{
    public $range;
    public $strict = false;
    public $not = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!is_array($this->range)
            && !($this->range instanceof \Closure)
            && !($this->range instanceof \Traversable)
        ) {
            throw new InvalidArgumentException('The "range" property must be set.');
        }
        if ($this->message === null) {
            $this->message ='{attribute} is invalid.';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $in = in_array($value,$this->range);
        return $this->not !== $in ? null : [$this->message, []];
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->range instanceof \Closure) {
            $this->range = call_user_func($this->range, $model, $attribute);
        }
        parent::validateAttribute($model, $attribute);
    }
}
