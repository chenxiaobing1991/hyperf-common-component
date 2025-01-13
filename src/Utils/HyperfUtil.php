<?php


namespace Cxb\Hyperf\Common\Utils;


/**
 * 基础工具类
 * Class Hyperf
 * @package App\Common\Helper
 */
class HyperfUtil
{


    /**
     * 类赋值
     * @param $object
     * @param $properties
     * @return mixed
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * Returns the public member variables of an object.
     * This method is provided such that we can get the public member variables of an object.
     * It is different from "get_object_vars()" because the latter will return private
     * and protected variables if it is called within the object itself.
     * @param object $object the object to be handled
     * @return array the public member variables of the object
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }

    /**
     * 实例化类
     * @param $type
     * @param $params
     */
    public static function createObject($type,array $params=[]){
        $object=null;
         if(is_string($type)){
             $object=new $type($params);
         }elseif(is_array($type)&&isset($type['class'])){
             $class=$type['class'];
             unset($type['class']);
             $params=array_merge($params,$type);
             $object=new $class($params);
         }
         return $object;
    }

    /**
     * 生成随机字符
     * @param $n
     * @return string
     */
    public static function  randomString($n){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
    /**
     * 判断是否序列化
     * @param type $data
     * @return boolean
     */
    public static function is_serialized($data) {
        $data = trim($data);
        if ('N;' == $data)
            return true;
        if (!preg_match('/^([adObis]):/', $data, $badions))
            return false;
        switch ($badions[1]) {
            case'a':
            case'O':
            case's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case'b':
            case'i':
            case'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }

    /**
     * 创建文件夹
     * @param string $path
     * @param int $priv
     */
    public static function mkdir(string $path,int $priv = 0755) {
        $path = rtrim($path, '/');
        $result = [];
        $fn=function($path) use(&$fn,&$result){
            if (!is_dir($path)) {
                array_unshift($result, $path);
            }
            if (!is_dir(dirname($path))) {
                $fn(dirname($path));
            }
        };
        $fn($path);
        foreach ($result as $value) {
            if (!is_dir($value)) {
                @mkdir($value, $priv);
            }
            @chmod($value, $priv);
        }
    }


}