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

namespace app\admin\model;


use app\admin\library\Auth;
use app\common\model\Dbase;

class AdminLog extends Dbase
{

    //自定义日志标题
    protected static $title = '';
    //自定义日志内容
    protected static $content = '';

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function setContent($content)
    {
        self::$content = $content;
    }

    public static function record()
    {
        $auth = Auth::instance();
        $admin_id = $auth->isLogin() ? $auth->id : 0;
        $username = $auth->isLogin() ? $auth->name : '-';
        $content = self::$content;
        if (!$content) {
            $content = request()->param('', null, 'trim,strip_tags,htmlspecialchars');
            foreach ($content as $k => $v) {
                if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false) {
                    unset($content[$k]);
                }
            }
        }
        $title = self::$title;
        if (!$title) {
            $title = [];
            $breadcrumb = Auth::instance()->getBreadcrumb();
            foreach ($breadcrumb as $k => $v) {
                $title[] = $v['name'];
            }
            $title = implode(' ', $title);
        }
        self::create([
            'title'     => $title,
            'body'   => !is_scalar($content) ? json_encode($content) : $content,
            'url'       => substr(request()->url(), 0, 1500),
            'admin_id'  => $admin_id,
            'name'  => $username,
            'useragent' => substr(request()->server('HTTP_USER_AGENT'), 0, 255),
            'ip'        => request()->ip()
        ]);
    }
}