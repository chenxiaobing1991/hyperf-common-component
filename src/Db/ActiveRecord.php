<?php
declare(strict_types=1);




namespace Cxb\Hyperf\Common\Db;


use Cxb\Hyperf\Common\Model;
use InvalidArgumentException;
/**
 * db中间适配器
 * Class ActiveRecord
 * @package App\Common\Base\db
 */
class ActiveRecord extends QueryActiveRecord
{
    use Model;
    /**
     * 获取对象
     * @param $condition
     */
    public static function findOne($condition){
            return static::findByCondition($condition)->first();
    }


    /**
     * @param $condition
     * @return mixed
     */
    public static function findAll($condition){
        return static::findByCondition($condition)->all();
    }

    /**
     * 查询
     * @param $condition
     */
    public static function findByCondition($condition){
        $primaryKey = static::primaryKey();
        if(!is_array($condition)){
            if(isset($primaryKey[0])){
                $pk = $primaryKey[0];
                $condition = [$pk => $condition];
            }else{
                throw new InvalidArgumentException('"' . get_called_class() . '" must have a primary key.');
            }
        }
        return static::query()->where($condition);
    }

    /**
     * 获取主键
     * @return array|mixed|null
     */
    public function getPrimaryKey(){
        $keys = static::primaryKey();
        $attributes=$this->getAttributes();
        if (count($keys) === 1) {
            return isset($attributes[$keys[0]]) ? $attributes[$keys[0]] : null;
        } else {
            $values = [];
            foreach ($keys as $name) {
                $values[$name] = isset($attributes[$name]) ? $attributes[$name] : null;
            }

            return $values;
        }
    }

    /**
     * 获取老版本主键
     * @return array|null
     */
    public function getOldPrimaryKey(){
        $keys = static::primaryKey();
        $oldAttributes=$this->getRawOriginal();
        if (count($keys) === 1) {
            return isset($oldAttributes[$keys[0]]) ? $oldAttributes[$keys[0]] : null;
        } else {
            $values = [];
            foreach ($keys as $name) {
                $values[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            }

            return $values;
        }
    }

    /**
     * 删除资源
     * @param $condition
     */
    public static function deleteAll(array $condition){
       return static::query()->andWhere($condition)->delete();
    }
    public static function updateAll(array $params,array $condition){
        return static::query()->andWhere($condition)->update($params);
    }


}