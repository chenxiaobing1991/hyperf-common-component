<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;


class NumberValidator extends Validator
{
    public $integerOnly = false;
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->integerOnly ? '{attribute} must be an integer.'
                : '{attribute} must be a number.';
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            $this->addError($model, $attribute, $this->message);
            return;
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, "$value")) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return ['{attribute} is invalid.', []];
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, "$value")) {
            return [$this->message, []];
        } elseif ($this->min !== null && $value < $this->min) {
            return [$this->tooSmall, ['min' => $this->min]];
        } elseif ($this->max !== null && $value > $this->max) {
            return [$this->tooBig, ['max' => $this->max]];
        } else {
            return null;
        }
    }
}
