<?php
declare(strict_types=1);




namespace Cxb\Hyperf\Common\Db;

use Hyperf\Database\Model\Builder as Builder1;



/**
 * db构建中间适配器
 * Class DbActiveRecord
 * @package App\Common\Base
 */
class QueryBuilder extends Builder1 implements QueryInterface
{

   use QueryTrait;

    /**
     * 带过滤器的条件与
     * @param array $condition
     * @return $this
     */
    public function andFilterWhere(array $condition){
        return $this->filterWhere($condition);
    }

    /**
     * 带过滤器的条件或
     * @param array $condition
     */
    public function orFilterWhere(array $condition){
        return $this->filterWhere($condition,'or');
    }

    /**
     * 带过滤器的条件重置
     * @param array $condition
     */
    public function filterWhere(array $condition=[],$boolean='and'){
        $condition=$this->filterCondition($condition);
        if($condition!==[])
            $this->connectCondition($condition,$boolean);//参数处理
        return $this;
    }

    /**
     * 不带过滤条件的过滤器
     * @param array $condition
     * @param string $boolean
     */
    public function andWhere(array $condition=[],$boolean='and'){
       return  $this->connectCondition($condition,$boolean);//参数处理
    }

    /**
     * 重构处理model
     * @return QueryBuilder[]|array|Builder1[]|\Hyperf\Database\Model\Model[]
     */
    public function all($columns=[])
    {
        $builder = $this->applyScopes();
        if (count($models = $builder->getModels($this->getColumns($columns))) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        return $models;
    }
    private function getQueryTableName(){
        $list=explode(' ',$this->query->from);
        $list=array_filter($list);
        $alias=$tableName=array_shift($list);
        if(!empty($list)){
            $alias=array_pop($list);
        }
        return [$tableName,$alias];
    }
    /**
     * 获取查找
     * @param array $columns
     */
    private function getColumns($columns=[]){
        $columns=!empty($columns)?$columns:$this->query->columns;
        if(empty($columns)){
            list(,$alias)=$this->getQueryTableName();
            $columns=["{$alias}.*"];
        }else{
            $columns=['*'];
        }
        return $columns;
    }

    /**
     * 重构查找字段
     * @param array $columns
     * @return QueryBuilder[]|\Hyperf\Database\Model\Collection
     */
    public function get($columns = [])
    {
        return parent::get($this->getColumns($columns));
    }


}