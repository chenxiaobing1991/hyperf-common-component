<?php


namespace Cxb\Hyperf\Common;

use Cxb\Hyperf\Common\Utils\HyperfUtil;

trait  Component
{
    public function __construct($config = [])
    {
        if (!empty($config)) {
            HyperfUtil::configure($this, $config);
        }
        $this->init();
    }
    /**
     * 内置初始化方法
     */
    protected function init(){

    }

    /**
     * 是否存在该方法
     * @param $name
     * @return bool
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}