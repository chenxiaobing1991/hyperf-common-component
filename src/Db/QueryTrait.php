<?php

declare(strict_types=1);




namespace Cxb\Hyperf\Common\Db;

/**
 * Trait QueryTrait
 * @package App\Common\Base\db
 */
trait QueryTrait
{


    /**
     * 过滤器处理
     * @param $condition
     */
    protected function filterCondition($condition)
    {
        if (!is_array($condition) || $condition instanceof \Closure) {
            return $condition;
        }
        /*  重构   */
        if (!isset($condition[0])) {
            foreach ($condition as $name => $value) {
                if ($this->isEmpty($value)) {
                    unset($condition[$name]);
                }
            }
            return $condition;
        }
        $operator = array_shift($condition);//链接类型
        switch (strtoupper($operator)) {
            case 'OR':
            case 'AND':
                foreach ($condition as $i => $operand) {
                    $subCondition = $this->filterCondition($operand);
                    if ($this->isEmpty($subCondition)) {
                        unset($condition[$i]);
                    } else {
                        $condition[$i] = $subCondition;
                    }
                }
                if (empty($condition)) {
                    return [];
                }
                break;
            default:
                $value = $operator == 'LIKE' ? trim($condition[1], '%') : $condition[1];
                if (array_key_exists(1, $condition) && $this->isEmpty($value)) {
                    return [];
                }
                break;
        }
        array_unshift($condition, $operator);
        return $condition;
    }

    /**
     * 链接资源
     * @param $condition
     * @param string $boolean
     */
    protected function connectCondition($condition, $boolean = 'and')
    {
        if (!is_array($condition) || $condition instanceof \Closure) {
            $this->where($condition, null, null, $boolean);
        }
        /*  键值对处理方式  */
        if (!isset($condition[0])) {
            foreach ($condition as $name => $value) {
                if (is_array($value))
                    $this->whereIn($name, $value, $boolean);
                else
                    $this->where($name, '=', $value, $boolean);

            }
            return $this;
        }
        $operator = array_shift($condition);
        if (in_array(strtoupper($operator), ['OR', 'AND'])) {
            foreach ($condition as $i => $operand) {
                $this->where(function ($query) use ($operand, $boolean) {
                    $query->connectCondition($operand, $boolean);
                    return $query;
                }, null, null, $operator);
            }
        } elseif (array_key_exists(1, $condition)) {
            switch (strtoupper($operator)) {
                case 'IN':
                    $this->whereIn($condition[0], $condition[1], $boolean);
                    break;
                case 'NOT IN':
                    $this->whereNotIn($condition[0], $condition[1], $boolean);
                    break;
                case 'BETWWEEN':
                    $this->whereBetween($condition[0], $condition[1], $boolean);
                    break;
                case 'NOT BETWWEEN':
                    $this->whereNotBetween($condition[0], $condition[1], $boolean);
                    break;
                default:
                    $this->where($condition[0], $operator, $condition[1], $boolean);
                    break;
            }
        }

        return $this;
    }

    /**
     * 判断是否为空
     * @param $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }
}