<?php

/**
 * LRCODE
 * ============================================================================
 * 版权所有 2016-2030 江苏蓝儒网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.lanru.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: 潇声
 * Date: 2020-01
 */

namespace app\common\library\token;


use think\facade\Config;

/**
 * Token基础类
 */
abstract class Driver
{
    protected $handler = null;
    protected $options = [];

    /**
     * 存储Token
     * @param   string $token   Token
     * @param   int    $user_id 会员ID
     * @param   int    $expire  过期时长,0表示无限,单位秒
     * @return bool
     */
    abstract function set($token, $user_id, $expire = 0);

    /**
     * 获取Token内的信息
     * @param   string $token
     * @return  array
     */
    abstract function get($token);

    /**
     * 判断Token是否可用
     * @param   string $token   Token
     * @param   int    $user_id 会员ID
     * @return  boolean
     */
    abstract function check($token, $user_id);

    /**
     * 删除Token
     * @param   string $token
     * @return  boolean
     */
    abstract function delete($token);

    /**
     * 删除指定用户的所有Token
     * @param   int $user_id
     * @return  boolean
     */
    abstract function clear($user_id);

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 获取加密后的Token
     * @param string $token Token标识
     * @return string
     */
    protected function getEncryptedToken($token)
    {
        $config = Config::get("token.");
        return hash_hmac($config['hashalgo'], $token, $config['key']);
    }

    /**
     * 获取过期剩余时长
     * @param $expiretime
     * @return float|int|mixed
     */
    protected function getExpiredIn($expiretime)
    {
        return $expiretime ? max(0, $expiretime - time()) : 365 * 86400;
    }
}