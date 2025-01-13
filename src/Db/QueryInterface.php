<?php
declare(strict_types=1);




namespace Cxb\Hyperf\Common\Db;

interface QueryInterface
{
   public function andFilterWhere(array $condition);//带过滤器的条件
   public function orFilterWhere(array $condition);//带过滤器的条件或
   public function filterWhere(array $condition);//带过滤器的条件重置


}