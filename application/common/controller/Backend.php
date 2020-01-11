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

namespace app\common\controller;


use app\admin\library\Auth;
use think\Db;
use think\db\Where;
use think\Exception;
use think\facade\Session;
use think\facade\Hook;
use think\Loader;

/**
 * 后台管理基类
 * @package app\common\controller
 */
class Backend extends BaseCtroller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;

    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;

    public function initialize()
    {
        parent::initialize();

        $this->assign('isDialog', $this->request->param("dialog", 0, 'intval'));

        $this->auth = Auth::instance();

        $modulename = $this->request->module();
        $controllername = Loader::parseName($this->request->controller());
        $actionname = strtolower($this->request->action());
        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        $this->assign('modulename', $modulename);

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //检测是否登录
            if (!$this->auth->isLogin()) {
                $url = Session::get('referer');
                $url = $url ? $url : $this->request->url();
                if ($url == '/') {
                    $this->redirect('index/login', [], 302, ['referer' => $url]);
                    exit;
                }
                $this->error('请登录...', url('index/login', ['url' => $url]));
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    Hook::listen('admin_nopermission', $this);
                    $this->error('无权限...', '');
                }
            }
        }

        // 设置面包屑导航数据
        $breadcrumb = $this->auth->getBreadCrumb($path);
        array_pop($breadcrumb);
        $this->assign('breadcrumb', $breadcrumb);

        //左侧菜单
        list($menulist, $selected, $referer) = $this->auth->getSidebar([],  '/' . $modulename . '/' . $path);
        $this->assign('menulist', $menulist);

        //渲染权限对象
        $this->assign('auth', $this->auth);
        //渲染管理员对象
        $this->assign('admin', Session::get('admin'));
    }

    /**
     * 添加
     * @return mixed|void
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $createData = $this->request->post('row/a');
            try{
                Db::transaction(function () use ($createData){

                    if ($this->model) {

                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));

                        $result = $this->validate($createData, $name . '.add');

                        if(true !== $result){
                            throw new Exception($result);
                        }

                        $res = $this->model->allowField(true)->save($createData);
                        if ($res === false)
                            throw new Exception($this->model->getError());
                    } else {
                        throw new Exception('缺少`$this->model`模型');
                    }

                });
            } catch (Exception $ex) {
                return $this->error($ex->getMessage());
            }

            return $this->success();
        }

        return $this->fetch();
    }

    /**
     * 编辑
     * @return mixed|void
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $row = $this->model->find($id);
        if (!$row) return $this->error('此记录不存在!无法编辑');
        if ($this->request->isAjax()) {
            $updata = $this->request->post('row/a');

            try{
                Db::transaction(function () use ($updata){

                    if ($this->model) {

                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $result = $this->validate($updata, $name . '.edit');

                        if(true !== $result){
                            throw new Exception($result);
                        }

                        $res = $this->model->allowField(true)->update($updata);
                        if ($res === false)
                            throw new Exception($this->model->getError());
                    } else {
                        throw new Exception('缺少`$this->model`模型');
                    }

                });
            } catch (Exception $ex) {
                return $this->error($ex->getMessage());
            }

            return $this->success();
        }

        $this->assign('row', $row);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del() {
        $id = $this->request->param('id');
        if (empty($id)) return $this->error('请选择删除的项目!');

        if (stripos($id, '~') !== false) $id = explode("~", $id);

        try {
            Db::transaction(function () use ($id) {
                $where = new Where();
                if (is_array($id)){
                    $where[$this->model->getPk()] = ['in', $id];
                } else {
                    $where[$this->model->getPk()] = $id;
                }

                $this->model->where($where)->delete();
                if (!empty($this->model->getError())) {
                    throw new Exception($this->model->getError());
                }
            });
        } catch (Exception $ex) {
            return $this->error($ex->getMessage());
        }

        return $this->success('删除成功!');

    }
}