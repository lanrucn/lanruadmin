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
 * Date: 2019-12
 */

namespace app\admin\validate;


use think\Validate;

class AdminRule extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'rule'  => 'require|unique:admin_rule,rule',
    ];

    protected $scene = [
        'edit'  =>  ['name', 'rule'],
        'add'  =>  ['name', 'rule'],
    ];

    public function __construct(array $rules = [], array $message = [], array $field = [])
    {
        $field = [
            'name' => '规则名称',
            'rule' => '规则',
        ];
        parent::__construct($rules, $message, $field);
    }
}