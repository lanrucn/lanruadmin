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

class UserGroup extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25|unique:user_group,name'
    ];

    protected $scene = [
        'edit'  =>  ['name'],
        'add'  =>  ['name'],
    ];

    public function __construct(array $rules = [], array $message = [], array $field = [])
    {
        $field = [
            'name' => '组名'
        ];
        parent::__construct($rules, $message, $field);
    }
}