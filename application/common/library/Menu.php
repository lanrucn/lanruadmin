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

namespace app\common\library;

use app\admin\model\AdminRule;
use lanru\Tree;
use think\Exception;

class Menu
{
    /**
     * 创建菜单
     * @param array $menu
     * @param mixed $parent 父类的name或pid
     */
    public static function create($menu, $parent = 0)
    {
        if (!is_numeric($parent)) {
            $parentRule = AdminRule::getByName($parent);
            $pid = $parentRule ? $parentRule['id'] : 0;
        } else {
            $pid = $parent;
        }
        $allow = array_flip(['name', 'rule', 'icon', 'remark', 'ismenu', 'status']);
        foreach ($menu as $k => $v) {
            $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;
            $data = array_intersect_key($v, $allow);
            $data['ismenu'] = isset($data['ismenu']) ? $data['ismenu'] : ($hasChild ? 1 : 0);
            $data['icon'] = isset($data['icon']) ? $data['icon'] : ($hasChild ? 'fa fa-list' : 'fa fa-circle-o');
            $data['pid'] = $pid;
            $data['status'] = isset($data['status']) ? $data['status'] : 0;
            $data['ismenu'] = isset($data['ismenu']) ? $data['ismenu'] : 0;
            try {
                $menu = AdminRule::create($data);
                if ($hasChild) {
                    self::create($v['sublist'], $menu->id);
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * 删除菜单
     * @param string $name 规则name
     * @return boolean
     */
    public static function delete($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AdminRule::destroy($ids);
        return true;
    }

    /**
     * 启用菜单
     * @param string $name
     * @return boolean
     */
    public static function enable($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AdminRule::where('id', 'in', $ids)->update(['status' => '1']);
        return true;
    }

    /**
     * 禁用菜单
     * @param string $name
     * @return boolean
     */
    public static function disable($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AdminRule::where('id', 'in', $ids)->update(['status' => '0']);
        return true;
    }

    /**
     * 根据名称获取规则IDS
     * @param string $name
     * @return array
     */
    public static function getAuthRuleIdsByName($name)
    {
        $ids = [];
        $menu = AdminRule::getByName($name);
        if ($menu) {
            // 必须将结果集转换为数组
            $ruleList = collection(AdminRule::order('weight', 'desc')->field('id,pid,name')->select())->toArray();
            // 构造菜单数据
            $ids = Tree::instance()->init($ruleList)->getChildrenIds($menu['id'], true);
        }
        return $ids;
    }
}