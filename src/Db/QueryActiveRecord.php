<?php
declare(strict_types=1);




namespace Cxb\Hyperf\Common\Db;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Model\Model as BaseModel;
/**
 * db查询适配器
 * Class QueryActiveRecord
 * @package App\Common\Base\db
 */
abstract class QueryActiveRecord  extends  BaseModel
{
    public const CREATED_AT =null;
    public const UPDATED_AT =null;
    public bool $incrementing = true;

    public bool $wasRecentlyCreated = false;
    protected array $guarded = [];
    /* **************    上面部分为配置项    ****************** */

    /**
     * 重置builder构建
     * @param \Hyperf\Database\Query\Builder $query
     * @return QueryBuilder
     */
    public function newModelBuilder($query)
    {
        return new QueryBuilder($query);
    }

    /**
     * 重写query查询
     * @return QueryActiveRecord|QueryBuilder|\Hyperf\Database\Model\Builder
     */
    public function newModelQuery()
    {
        $this->setTable(static::tableName());
        return $this->newModelBuilder($this->newBaseQueryBuilder())->setModel($this);
    }

    /**
     * 获取表名
     */
    public static function tableName(){
        return '';
    }

    /**
     * 获取主键类型
     * @return \stdClass
     */
    public static function getTableSchema(){
        $list= Schema::getColumnTypeListing(static::tableName());
        $model=new \stdClass();
        $model->name=static::tableName();
        $model->primaryKey=[];
        $model->columns=[];
        foreach($list as $info){
            if($info['column_key']=='PRI'){
                $model->primaryKey[]=$info['column_name'];
            }
            $model->columns[]=$info;
        }
        return $model;

    }
    /**
     * 获取主键
     */
    public static function primaryKey(){
        return self::getTableSchema()->primaryKey;
    }
    /**
     * Set the keys for a save update query.
     *支持联合主键
     * @param \Hyperf\Database\Model\Builder $query
     * @return \Hyperf\Database\Model\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = static::primaryKey();
        foreach($keys as $key){
            $query->where($keys[0], '=',$this->original[$key]);
        }
        return $query;
    }

    //getDirty();本次修改

}