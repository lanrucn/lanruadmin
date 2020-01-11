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

namespace app\admin\library;


use app\admin\model\Admin;
use app\admin\model\AdminGroup;
use app\admin\model\AdminRule;
use lanru\Random;
use lanru\Tree;
use think\facade\Cookie;
use think\facade\Hook;
use think\facade\Request;
use think\facade\Session;
use app\admin\model\AdminGroupAccess;

class Auth extends \lanru\Auth
{
    protected $_error = '';
    protected $requestUri = '';
    protected $breadcrumb = [];
    protected $logined = false; //登录状态

    public function __construct()
    {
        parent::__construct();
    }

    public function __get($name)
    {
        return Session::get('admin.' . $name);
    }

    /**
     * 管理员登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param int    $keeptime 有效时长
     * @return  boolean
     */
    public function login($username, $password, $keeptime = 0)
    {
        $admin = Admin::get(['name' => $username]);
        if (!$admin) {
            $this->setError('管理员不存在');
            return false;
        }
        if (!$admin['status']) {
            $this->setError('管理员已被禁止');
            return false;
        }
        if ($admin->login_failure >= 10 && time() - $admin->edittime < 86400) {
            $this->setError('密码错误超过10次!无法登录');
            return false;
        }

        if ($admin->password != md5(md5($password) . $admin->salt)) {
            $admin->login_failure++;
            $admin->save();
            $this->setError('密码错误' + $admin->login_failure + '次');
            return false;
        }
        $admin->login_failure = 0;
        $admin->login_time = time();
        $admin->login_ip = request()->ip();
        $admin->token = Random::uuid();
        $admin->save();
        Session::set("admin", $admin->toArray());
        $this->keeplogin($keeptime);
        return true;
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $admin = Admin::get(intval($this->id));
        if ($admin) {
            $admin->token = '';
            $admin->save();
        }
        $this->logined = false; //重置登录状态
        Session::delete("admin");
        Cookie::delete("keeplogin");
        return true;
    }

    /**
     * 自动登录
     * @return boolean
     */
    public function autologin()
    {
        $keeplogin = Cookie::get('keeplogin');
        if (!$keeplogin) {
            return false;
        }
        list($id, $keeptime, $expiretime, $key) = explode('|', $keeplogin);
        if ($id && $keeptime && $expiretime && $key && $expiretime > time()) {
            $admin = Admin::get($id);
            if (!$admin || !$admin->token) {
                return false;
            }
            //token有变更
            if ($key != md5(md5($id) . md5($keeptime) . md5($expiretime) . $admin->token)) {
                return false;
            }
            $ip = request()->ip();
            //IP有变动
            if ($admin->login_ip != $ip) {
                return false;
            }
            Session::set("admin", $admin->toArray());
            //刷新自动登录的时效
            $this->keeplogin($keeptime);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 刷新保持登录的Cookie
     *
     * @param int $keeptime
     * @return  boolean
     */
    protected function keeplogin($keeptime = 0)
    {
        if ($keeptime) {
            $expiretime = time() + $keeptime;
            $key = md5(md5($this->id) . md5($keeptime) . md5($expiretime) . $this->token);
            $data = [$this->id, $keeptime, $expiretime, $key];
            Cookie::set('keeplogin', implode('|', $data), 86400 * 30);
            return true;
        }
        return false;
    }

    public function check($name, $uid = '', $relation = 'or', $mode = 'url')
    {
        $uid = $uid ? $uid : $this->id;
        return parent::check($name, $uid, $relation, $mode);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return bool
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }

        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 检测是否登录
     *
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }
        $admin = Session::get('admin');
        if (!$admin) {
            return false;
        }
        //判断是否同一时间同一账号只能在一个地方登录
        $my = Admin::get($admin['id']);
        if (!$my || !isset($my['token']) || $my['token'] != $admin['token']) {
            $this->logout();
            return false;
        }

        //判断管理员IP是否变动
        if (!isset($admin['login_ip']) || $admin['login_ip'] != request()->ip()) {
            $this->logout();
            return false;
        }
        $this->logined = true;
        return true;
    }

    /**
     * 获取当前请求的URI
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    public function getGroups($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getGroups($uid);
    }

    public function getRuleList($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleList($uid);
    }

    public function getUserInfo($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;

        return $uid != $this->id ? Admin::get(intval($uid)) : Session::get('admin');
    }

    public function getRuleIds($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleIds($uid);
    }

    public function isSuperAdmin()
    {
        return in_array('*', $this->getRuleIds()) ? true : false;
    }

    /**
     * 获取管理员所属于的分组ID
     * @param int $uid
     * @return array
     */
    public function getGroupIds($uid = null)
    {
        $groups = $this->getGroups($uid);
        $groupIds = [];
        foreach ($groups as $K => $v) {
            $groupIds[] = (int)$v['group_id'];
        }
        return $groupIds;
    }

    /**
     * 取出当前管理员所拥有权限的分组
     * @param boolean $withself 是否包含当前所在的分组
     * @return array
     */
    public function getChildrenGroupIds($withself = false)
    {
        //取出当前管理员所有的分组
        $groups = $this->getGroups();
        $groupIds = [];
        foreach ($groups as $k => $v) {
            $groupIds[] = $v['id'];
        }
        $originGroupIds = $groupIds;
        foreach ($groups as $k => $v) {
            if (in_array($v['pid'], $originGroupIds)) {
                $groupIds = array_diff($groupIds, [$v['id']]);
                unset($groups[$k]);
            }
        }

        // 取出所有分组
        $groupList = AdminGroup::where('status', 1)->select();
        $objList = [];
        foreach ($groups as $k => $v) {
            if ($v['rules'] === '*') {
                $objList = $groupList;
                break;
            }
            // 取出包含自己的所有子节点
            $childrenList = Tree::instance()->init($groupList)->getChildren($v['id'], true);
            $obj = Tree::instance()->init($childrenList)->getTreeArray($v['pid']);
            $objList = array_merge($objList, Tree::instance()->getTreeList($obj));
        }
        $childrenGroupIds = [];
        foreach ($objList as $k => $v) {
            $childrenGroupIds[] = $v['id'];
        }
        if (!$withself) {
            $childrenGroupIds = array_diff($childrenGroupIds, $groupIds);
        }
        return $childrenGroupIds;
    }

    /**
     * 取出当前管理员所拥有权限的管理员
     * @param boolean $withself 是否包含自身
     * @return array
     */
    public function getChildrenAdminIds($withself = false)
    {
        $childrenAdminIds = [];
        if (!$this->isSuperAdmin()) {
            $groupIds = $this->getChildrenGroupIds(false);
            $authGroupList = AdminGroupAccess::
            field('uid,group_id')
                ->where('group_id', 'in', $groupIds)
                ->select();
            foreach ($authGroupList as $k => $v) {
                $childrenAdminIds[] = $v['uid'];
            }
        } else {
            //超级管理员拥有所有人的权限
            $childrenAdminIds = Admin::column('id');
        }
        if ($withself) {
            if (!in_array($this->id, $childrenAdminIds)) {
                $childrenAdminIds[] = $this->id;
            }
        } else {
            $childrenAdminIds = array_diff($childrenAdminIds, [$this->id]);
        }
        return $childrenAdminIds;
    }

    /**
     * 获得面包屑导航
     * @param string $path
     * @return array
     */
    public function getBreadCrumb($path = '')
    {
        if ($this->breadcrumb || !$path) {
            return $this->breadcrumb;
        }
        $titleArr = [];
        $menuArr = [];
        $urlArr = explode('/', $path);
        foreach ($urlArr as $index => $item) {
            $pathArr[implode('/', array_slice($urlArr, 0, $index + 1))] = $index;
        }
        if (!$this->rules && $this->id) {
            $this->getRuleList();
        }

        foreach ($this->rules as $rule) {
            if (isset($pathArr[$rule['rule']])) {
                $rule['title'] = $rule['name'];
                $rule['url'] = url($rule['rule']);
                $titleArr[$pathArr[$rule['rule']]] = $rule['title'];
                $menuArr[$pathArr[$rule['rule']]] = $rule;
            }

        }
        ksort($menuArr);
        $this->breadcrumb = $menuArr;
        return $this->breadcrumb;
    }

    /**
     * 获取左侧和顶部菜单栏
     *
     * @param array  $params    URL对应的badge数据
     * @param string $fixedPage 默认页
     * @return array
     */
    public function getSidebar($params = [], $fixedPage = 'index')
    {
        // 边栏开始
        Hook::listen("admin_sidebar_begin", $params);
        $colorArr = ['red', 'green', 'yellow', 'blue', 'teal', 'orange', 'purple'];
        $colorNums = count($colorArr);
        $badgeList = [];
        $module = request()->module();

        // 生成菜单的badge
        foreach ($params as $k => $v) {
            $url = $k;
            if (is_array($v)) {
                $nums = isset($v[0]) ? $v[0] : 0;
                $color = isset($v[1]) ? $v[1] : $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = isset($v[2]) ? $v[2] : 'label';
            } else {
                $nums = $v;
                $color = $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = 'label';
            }
            //必须nums大于0才显示
            if ($nums) {
                $badgeList[$url] = '<small class="' . $class . ' pull-right bg-' . $color . '">' . $nums . '</small>';
            }
        }

        // 读取管理员当前拥有的权限节点
        $userRule = $this->getRuleList();
        $selected = $referer = [];
        $refererUrl = Session::get('referer');

        // 必须将结果集转换为数组
        $ruleList = collection(AdminRule::where('status', 1)
            ->where('ismenu', 1)
            ->order('weight', 'desc')
            ->cache("__menu__")
            ->select())->toArray();
        $indexRuleList = AdminRule::where('status', 1)
            ->where('ismenu', 1)
            ->where('name', 'like', '%/index')
            ->column('name,pid');
        $pidArr = array_filter(array_unique(array_map(function ($item) {
            return $item['pid'];
        }, $ruleList)));
        foreach ($ruleList as $k => &$v) {
            if (!in_array($v['name'], $userRule)) {
                unset($ruleList[$k]);
                continue;
            }
            $indexRuleName = $v['rule'] . '/index';
            if (isset($indexRuleList[$indexRuleName]) && !in_array($indexRuleName, $userRule)) {
                unset($ruleList[$k]);
                continue;
            }
            $v['icon'] = $v['icon'];
            $v['url'] = '/' . $module . '/' . $v['rule'];
            $v['title'] = $v['name'];

            $selected = stripos($fixedPage, $v['url']) !== false ? $v : $selected;
            $referer = url($v['url']) == $refererUrl ? $v : $referer;
        }
        $lastArr = array_diff($pidArr, array_filter(array_unique(array_map(function ($item) {
            return $item['pid'];
        }, $ruleList))));
        foreach ($ruleList as $index => $item) {
            if (in_array($item['id'], $lastArr)) {
                unset($ruleList[$index]);
            }

            $ruleList[$index]['isopen'] = isset($selected['pid']) && $item['id'] == $selected['pid'] ? 1 : 0;
        }

        if ($selected == $referer) {
            $referer = [];
        }
        $selected && $selected['url'] = url($selected['url']);
        $referer && $referer['url'] = url($referer['url']);

        $select_id = $selected ? $selected['id'] : 0;
        $menu = '';
        // 构造菜单数据
        Tree::instance()->init($ruleList);
        $menu = Tree::instance()->getTreeMenu(
            0,
            '<li class="@class"><a@url><i class="@icon"></i><span>@title</span> @caret</a>@childlist</li>',
            $select_id,
            '',
            'ul',
            'class="treeview-menu"'
        );
        return [$menu, $selected, $referer];
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? $this->_error : '';
    }
}