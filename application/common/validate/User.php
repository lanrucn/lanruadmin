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

namespace app\common\validate;


use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule =   [
        'name'  => 'require|regex:\w{3,60}|unique:user,name',
        'email'  => 'require|email|unique:user,email',
        'mobile'  => 'require|unique:user,mobile',
        'password' => 'require|regex:\S{6,32}'
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name', 'email', 'mobile', 'password'],
        'edit' => ['name', 'email', 'mobile'],
    ];

    public function __construct(array $rules = [], array $message = [], array $field = [])
    {
        $field = [
            'name' => '用户名',
            'email' => 'E-mail',
            'mobile' => '手机号'
        ];
        $this->message = array_merge($this->message, [
            'name.regex' => '用户名只能由3-60位数字、字母、下划线组合',
            'password.regex' => '密码长度必须在6-16位之间，不能包含空格'
        ]);

        parent::__construct($rules, $message, $field);
    }
}