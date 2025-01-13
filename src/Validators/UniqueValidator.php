<?php
declare(strict_types=1);
namespace Cxb\Hyperf\Common\Validators;

use Hyperf\Database\Model\Model as ActiveRecordInterface;
class UniqueValidator extends Validator
{
    public $targetClass;
    public $targetAttribute;
    public $filter;
    public $comboNotUnique;

    public $allowArray=false;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message ='{attribute} "{value}" has already been taken.';
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $targetAttribute = $this->targetAttribute === null ? [$attribute] : $this->targetAttribute;

        if (is_array($targetAttribute)) {
            $params = [];
            foreach ($targetAttribute as $k => $v) {
                $params[$v] = is_int($k) ? $model->$v : $model->$k;
            }
        }
        foreach ($params as $value) {
            if (is_array($value)&&!$this->allowArray) {
                $this->addError($model, $attribute, '{attribute} is invalid.');
                return;
            }
        }

        $query = $targetClass::query();
        $query->where($params);

        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }
        if (!$model instanceof ActiveRecordInterface || $model->getIsNewRecord() || $model::className() !== $targetClass::className()) {
            $exists = $query->exists();
        } else {
            $models = $query->limit(2)->all();
            $n = count($models);
            if ($n === 1) {
                $keys = array_keys($params);
                $pks = $targetClass::primaryKey();
                sort($keys);
                sort($pks);
                if ($keys === $pks) {
                    // primary key is modified and not unique
                    $exists = $model->getOldPrimaryKey() != $model->getPrimaryKey();
                } else {
                    // non-primary key, need to exclude the current record based on PK
                    $exists = $models[0]->getPrimaryKey() != $model->getOldPrimaryKey();
                }
            } else {
                $exists = $n > 1;
            }
        }
        if ($exists) {
            if (count($targetAttribute) > 1) {
                $this->addComboNotUniqueError($model, $attribute);
            } else {
                $this->addError($model, $attribute, $this->message,['value'=>$model->$attribute]);
            }
        }
    }
    
    /**
     * Builds and adds [[comboNotUnique]] error message to the specified model attribute.
     * @param \yii\base\Model $model the data model.
     * @param string $attribute the name of the attribute.
     */
    private function addComboNotUniqueError($model, $attribute)
    {
        $attributeCombo = [];
        $valueCombo = [];
        foreach ($this->targetAttribute as $key => $value) {
            if(is_int($key)) {
                $attributeCombo[] = $model->getAttributeLabel($value);
                $valueCombo[] = '"' . $model->$value . '"';
            } else {
                $attributeCombo[] = $model->getAttributeLabel($key);
                $valueCombo[] = '"' . $model->$key . '"';
            }
        }
        $this->addError($model, $attribute, $this->message);
    }
}
