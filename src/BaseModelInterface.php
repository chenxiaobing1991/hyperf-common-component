<?php



namespace Cxb\Hyperf\Common;
/**
 * 底层公共主键
 * Interface BaseModelInterface
 * @author chenxiaobing <2362584113@qq.com>
 */
interface  BaseModelInterface {

    public function addError($column,$msg);//添加错误日志
    public function getFirstErrors();//获取全部错误日志
    public function validate();//数据校验
    public function scenarios();//方案注册
    public function hasErrors();//是否有错误信息
    public function setErrors($errors);//重置错误日志
    public function rules();//规则定义

}