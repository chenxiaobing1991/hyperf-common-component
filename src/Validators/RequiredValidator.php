<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;
class RequiredValidator extends Validator
{
    public $skipOnEmpty = false;
    public $requiredValue;
    public $strict = false;
    public $message;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->requiredValue === null ? '{attribute} cannot be blank.'
                :'{attribute} must be "{requiredValue}".';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if ($this->requiredValue === null) {
            if ($this->strict && $value !== null || !$this->strict && !$this->isEmpty(is_string($value) ? trim($value) : $value)) {
                return null;
            }
        } elseif (!$this->strict && $value == $this->requiredValue || $this->strict && $value === $this->requiredValue) {
            return null;
        }
        if ($this->requiredValue === null) {
            return [$this->message, []];
        } else {
            return [$this->message, [
                'requiredValue' => $this->requiredValue,
            ]];
        }
    }
}
