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
use lanru\Form;

if (!function_exists('build_select')) {

    /**
     * 生成下拉列表
     * @param string $name
     * @param mixed $options
     * @param mixed $selected
     * @param mixed $attr
     * @return string
     */
    function build_select($name, $options, $selected = [], $attr = [])
    {
        $options = is_array($options) ? $options : explode(',', $options);
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        return Form::select($name, $options, $selected, $attr);
    }
}

if (!function_exists('build_radios')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_radios($name, $list = [], $selected = null)
    {
        $html = [];
        $selected = is_null($selected) ? key($list) : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::radio($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="radio">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists('build_checkboxs')) {

    /**
     * 生成复选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_checkboxs($name, $list = [], $selected = null)
    {
        $html = [];
        $selected = is_null($selected) ? [] : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::checkbox($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="checkbox">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists("bulid_status")) {

    /**
     * 生成状态Html
     * @param int $value
     * @return string
     */
    function bulid_status($value, $default = 0) {
        if ($default == 0) {
            $html = '<span class="text-success"><i class="fa fa-circle"></i> 正常</span>';

            switch ($value) {
                case 0:
                    $html = '<span class="text-gray"><i class="fa fa-circle"></i> 禁用</span>';
                    break;
            }
        } else {
            $html = '<span class="text-success"><i class="fa fa-circle"></i> 是</span>';
            $value == 0 and $html = '<span class="text-gray"><i class="fa fa-circle"></i> 否</span>';
        }


        return $html;
    }
}


if (!function_exists("bulid_menu")) {

    /**
     * 生成操作按钮栏
     * @param array $btns 按钮组
     * @param array $attr 按钮属性值
     * @return string
     */
    function bulid_menu($btns, $attr = []) {
        $auth = \app\admin\library\Auth::instance();
        $request = \think\facade\Request::instance();
        $modulename = $request->module();
        $controller = str_replace('.', '/', strtolower($request->controller()));
        $btns = $btns ? $btns : ['refresh', 'add', 'del'];
        $btns = is_array($btns) ? $btns : explode(',', $btns);
        $index = array_search('del', $btns);
        if ($index !== false) {
            $btns[$index] = 'del';
        }
        $btnAttr = [
            'refresh' => ['javascript:;', 'btn btn-sm btn-primary btn-refresh', 'fa fa-refresh', '', '刷新'],
            'add'     => ['javascript:;', 'btn btn-sm btn-dialog btn-success', 'fa fa-plus', '添加', '添加'],
            'del'     => ['javascript:;', 'btn btn-sm btn-danger btn-delete-action', 'fa fa-trash', '删除', '删除'],
        ];
        $btnAttr = array_merge($btnAttr, $attr);
        $html = [];

        foreach ($btns as $k => $v) {
            //如果未定义或没有权限
            if (!isset($btnAttr[$v]) || ($v !== 'refresh' && !$auth->check("{$controller}/{$v}"))) {
                continue;
            }
            list($href, $class, $icon, $text, $title) = $btnAttr[$v];
            $data_href = url($modulename . '/' . $request->controller() . '/' . $v);
            $data_href = $v == "refresh" ? "" : " data-href='{$data_href}'";

            $html[] = '<a href="' . $href . '"' .$data_href. ' class="' . $class . '" title="' . $title . '"><i class="' . $icon . '"></i> ' . $text . '</a>';
        }
        return implode(' ', $html);
    }
}