<?php
declare(strict_types=1);
namespace Cxb\Hyperf\Common\Validators;
use InvalidArgumentException;

/**
 * 正则表达式
 * Class RegularExpressionValidator
 * @package App\Common\Validators
 */
class RegularExpressionValidator extends Validator
{
    public $pattern;
    public $not = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->pattern === null) {
            throw new InvalidArgumentException('The "pattern" property must be set.');
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
        $valid = !is_array($value) &&
            (!$this->not && preg_match($this->pattern, $value)
            || $this->not && !preg_match($this->pattern, $value));

        return $valid ? null : [$this->message, []];
    }
}
