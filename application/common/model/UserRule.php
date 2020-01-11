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

namespace app\common\model;


use lanru\Tree;

class UserRule extends Dbase
{

    public function getStatusList()
    {
        return [0 => '隐藏', 1 => '正常'];
    }

    public static function getTreeList($selected = [])
    {
        $ruleList = collection(self::where('status', 1)->order('weight desc,id desc')->select())->toArray();
        $nodeList = [];
        Tree::instance()->init($ruleList);
        $ruleList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $hasChildrens = [];
        foreach ($ruleList as $k => $v)
        {
            if ($v['haschild'])
                $hasChildrens[] = $v['id'];
        }
        foreach ($ruleList as $k => $v) {
            $state = array('selected' => in_array($v['id'], $selected) && !in_array($v['id'], $hasChildrens));
            $nodeList[] = array('id' => $v['id'], 'parent' => $v['pid'] ? $v['pid'] : '#', 'text' => $v['name'], 'type' => 'menu', 'state' => $state);
        }
        return $nodeList;
    }
}