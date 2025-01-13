<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;
class StringValidator extends Validator
{
    public $length;
    public $max;
    public $min;
    public $message;
    public $tooShort;
    public $tooLong;
    public $notEqual;
    public $encoding='utf-8';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (is_array($this->length)) {
            if (isset($this->length[0])) {
                $this->min = $this->length[0];
            }
            if (isset($this->length[1])) {
                $this->max = $this->length[1];
            }
            $this->length = null;
        }
        if ($this->message === null) {
            $this->message ='{attribute} must be a string.';
        }
        if ($this->min !== null && $this->tooShort === null) {
            $this->tooShort = '{attribute} should contain at least {min, number} {min, plural, one{character} other{characters}}.';
        }
        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong ='{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.';
        }
        if ($this->length !== null && $this->notEqual === null) {
            $this->notEqual ='{attribute} should contain {length, number} {length, plural, one{character} other{characters}}.';
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if(is_null($value))
            return ;
        if (!is_string($value)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }
        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            $this->addError($model, $attribute, $this->tooShort, ['min' => $this->min]);
        }
        if ($this->max !== null && $length > $this->max) {
            $this->addError($model, $attribute, $this->tooLong, ['max' => $this->max]);
        }
        if ($this->length !== null && $length !== $this->length) {
            $this->addError($model, $attribute, $this->notEqual, ['length' => $this->length]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            return [$this->tooShort, ['min' => $this->min]];
        }
        if ($this->max !== null && $length > $this->max) {
            return [$this->tooLong, ['max' => $this->max]];
        }
        if ($this->length !== null && $length !== $this->length) {
            return [$this->notEqual, ['length' => $this->length]];
        }

        return null;
    }
}
