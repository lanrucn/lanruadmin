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

namespace app\admin\controller;


use app\common\controller\Backend;

class Menu extends Backend
{
    public function index()
    {
        $menu = [
            [
                'name'    => '控制台',
                'rule'   => 'index',
                'icon'    => 'fa fa-dashboard',
                'sublist' => [
                    ['rule' => 'index/index', 'name' => '查看']
                ],
                'ismenu' => 1,
                'status' => 1
            ],
            [
                'name'    => '系统配置',
                'rule'   => 'general',
                'icon'    => 'fa fa-cogs',
                'sublist' => [
                    [
                        'name'    => '基本配置',
                        'rule'   => 'general/config',
                        'icon'    => 'fa fa-dashboard',
                        'sublist' => [
                            ['rule' => 'general/config/index', 'name' => '查看'],
                            ['rule' => 'general/config/create', 'name' => '添加'],
                            ['rule' => 'general/config/edit', 'name' => '编辑'],
                            ['rule' => 'general/config/add', 'name' => '添加'],
                            ['rule' => 'general/config/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '附件管理',
                        'rule'   => 'general/attachment',
                        'icon'    => 'fa fa-file-image-o',
                        'sublist' => [
                            ['rule' => 'general/attachment/index', 'name' => '查看'],
                            ['rule' => 'general/attachment/create', 'name' => '添加'],
                            ['rule' => 'general/attachment/edit', 'name' => '编辑'],
                            ['rule' => 'general/attachment/add', 'name' => '添加'],
                            ['rule' => 'general/attachment/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '个人设置',
                        'rule'   => 'general/profile',
                        'icon'    => 'fa fa-user',
                        'sublist' => [
                            ['rule' => 'general/profile/index', 'name' => '查看'],
                            ['rule' => 'general/profile/create', 'name' => '添加'],
                            ['rule' => 'general/profile/edit', 'name' => '编辑'],
                            ['rule' => 'general/profile/add', 'name' => '添加'],
                            ['rule' => 'general/profile/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                ],
                'ismenu' => 1,
                'status' => 1
            ],
            [
                'name'    => '权限管理',
                'rule'   => 'auth',
                'icon'    => 'fa fa-group',
                'sublist' => [
                    [
                        'name'    => '管理员管理',
                        'rule'   => 'auth/admin',
                        'icon'    => 'fa fa-user',
                        'sublist' => [
                            ['rule' => 'auth/admin/index', 'name' => '查看'],
                            ['rule' => 'auth/admin/create', 'name' => '添加'],
                            ['rule' => 'auth/admin/edit', 'name' => '编辑'],
                            ['rule' => 'auth/admin/add', 'name' => '添加'],
                            ['rule' => 'auth/admin/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '日志管理',
                        'rule'   => 'auth/adminlog',
                        'icon'    => 'fa fa-list-alt',
                        'sublist' => [
                            ['rule' => 'auth/adminlog/index', 'name' => '查看'],
                            ['rule' => 'auth/adminlog/create', 'name' => '添加'],
                            ['rule' => 'auth/adminlog/edit', 'name' => '编辑'],
                            ['rule' => 'auth/adminlog/add', 'name' => '添加'],
                            ['rule' => 'auth/adminlog/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '管理角色',
                        'rule'   => 'auth/group',
                        'icon'    => 'fa fa-group',
                        'sublist' => [
                            ['rule' => 'auth/group/index', 'name' => '查看'],
                            ['rule' => 'auth/group/create', 'name' => '添加'],
                            ['rule' => 'auth/group/edit', 'name' => '编辑'],
                            ['rule' => 'auth/group/add', 'name' => '添加'],
                            ['rule' => 'auth/group/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '菜单管理',
                        'rule'   => 'auth/rule',
                        'icon'    => 'fa fa-bars',
                        'sublist' => [
                            ['rule' => 'auth/rule/index', 'name' => '查看'],
                            ['rule' => 'auth/rule/create', 'name' => '添加'],
                            ['rule' => 'auth/rule/edit', 'name' => '编辑'],
                            ['rule' => 'auth/rule/add', 'name' => '添加'],
                            ['rule' => 'auth/rule/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                ],
                'ismenu' => 1,
                'status' => 1
            ],
            [
                'name'    => '管理会员',
                'rule'   => 'user',
                'icon'    => 'fa fa-list',
                'sublist' => [
                    [
                        'name'    => '会员列表',
                        'rule'   => 'user/user',
                        'icon'    => 'fa fa-user',
                        'sublist' => [
                            ['rule' => 'user/user/index', 'name' => '查看'],
                            ['rule' => 'user/user/create', 'name' => '添加'],
                            ['rule' => 'user/user/edit', 'name' => '编辑'],
                            ['rule' => 'user/user/add', 'name' => '添加'],
                            ['rule' => 'user/user/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '会员组管理',
                        'rule'   => 'user/group',
                        'icon'    => 'fa fa-users',
                        'sublist' => [
                            ['rule' => 'user/group/index', 'name' => '查看'],
                            ['rule' => 'user/group/create', 'name' => '添加'],
                            ['rule' => 'user/group/edit', 'name' => '编辑'],
                            ['rule' => 'user/group/add', 'name' => '添加'],
                            ['rule' => 'user/group/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ],
                    [
                        'name'    => '会员权限',
                        'rule'   => 'user/rule',
                        'icon'    => 'fa fa-circle-o',
                        'sublist' => [
                            ['rule' => 'user/rule/index', 'name' => '查看'],
                            ['rule' => 'user/rule/create', 'name' => '添加'],
                            ['rule' => 'user/rule/edit', 'name' => '编辑'],
                            ['rule' => 'user/rule/add', 'name' => '添加'],
                            ['rule' => 'user/rule/delete', 'name' => '删除'],
                        ],
                        'ismenu' => 1,
                        'status' => 1
                    ]
                ],
                'ismenu' => 1,
                'status' => 1
            ],
            [
                'name'    => '插件管理',
                'rule'   => 'addon',
                'icon'    => 'fa fa-rocket',
                'sublist' => [
                    ['rule' => 'addon/index', 'name' => '查看'],
                    ['rule' => 'addon/install', 'name' => '安装'],
                    ['rule' => 'addon/config', 'name' => '配置'],
                    ['rule' => 'addon/delete', 'name' => '删除'],
                ],
                'ismenu' => 1,
                'status' => 1
            ]
        ];

        \app\common\library\Menu::create($menu, 0);
        echo "ok";
    }
}