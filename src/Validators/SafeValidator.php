<?php
declare(strict_types=1);


namespace Cxb\Hyperf\Common\Validators;
/**
 * 通过权限,不尽兴校验
 * Class SafeValidator
 * @package App\Common\Validators
 */
class SafeValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
    }
}
