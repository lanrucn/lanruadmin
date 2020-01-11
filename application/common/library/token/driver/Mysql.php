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

namespace app\common\library\token\driver;


use app\common\library\token\Driver;
use think\Db;

class Mysql extends Driver
{
    /**
     * 默认配置
     * @var array
     */
    protected $options = [
        'table'      => 'user_token',
        'expire'     => 2592000,
        'connection' => [],
    ];

    /**
     * 构造函数
     * @param array $options 参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if ($this->options['connection']) {
            $this->handler = Db::connect($this->options['connection'])->name($this->options['table']);
        } else {
            $this->handler = Db::name($this->options['table']);
        }
    }

    /**
     * 存储Token
     * @param   string $token   Token
     * @param   int    $user_id 会员ID
     * @param   int    $expire  过期时长,0表示无限,单位秒
     * @return bool
     */
    public function set($token, $user_id, $expire = null)
    {
        $expiretime = !is_null($expire) && $expire !== 0 ? time() + $expire : 0;
        $token = $this->getEncryptedToken($token);
        $this->handler->insert(['token' => $token, 'user_id' => $user_id, 'createtime' => time(), 'expiretime' => $expiretime]);
        return TRUE;
    }

    /**
     * 获取Token内的信息
     * @param   string $token
     * @return  array
     */
    public function get($token)
    {
        $data = $this->handler->where('token', $this->getEncryptedToken($token))->find();
        if ($data) {
            if (!$data['expiretime'] || $data['expiretime'] > time()) {
                //返回未加密的token给客户端使用
                $data['token'] = $token;
                //返回剩余有效时间
                $data['expires_in'] = $this->getExpiredIn($data['expiretime']);
                return $data;
            } else {
                self::delete($token);
            }
        }
        return [];
    }

    /**
     * 判断Token是否可用
     * @param   string $token   Token
     * @param   int    $user_id 会员ID
     * @return  boolean
     */
    public function check($token, $user_id)
    {
        $data = $this->get($token);
        return $data && $data['user_id'] == $user_id ? true : false;
    }

    /**
     * 删除Token
     * @param   string $token
     * @return  boolean
     */
    public function delete($token)
    {
        $this->handler->where('token', $this->getEncryptedToken($token))->delete();
        return true;
    }

    /**
     * 删除指定用户的所有Token
     * @param   int $user_id
     * @return  boolean
     */
    public function clear($user_id)
    {
        $this->handler->where('user_id', $user_id)->delete();
        return true;
    }
}