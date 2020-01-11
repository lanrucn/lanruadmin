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

namespace think;

define("LRROOT", __DIR__);

// 判断是否安装LanruAdmin
if (!is_file(LRROOT . '/../extend/install/install.lock'))
{
    header("location:./install.php");
    exit;
}

define("DS", DIRECTORY_SEPARATOR);
// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';
// 执行应用并响应
Container::get('app')->bind('admin')->run()->send();
