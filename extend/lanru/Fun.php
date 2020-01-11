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

namespace lanru;


class Fun
{
    /**
     * @brief 判断是否为手机端
     * @return bool
     */
    public static function isMobile()
    {
        static $is_mobile;

        if (isset($is_mobile)) return $is_mobile;

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $is_mobile = false;
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {

            $is_mobile = true;
        } else {
            $is_mobile = false;
        }

        return $is_mobile;

    }
}