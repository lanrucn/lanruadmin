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

namespace app\admin\controller\general;


use app\admin\model\Admin;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use lanru\Random;
use think\db\Where;
use think\facade\Validate;

class Profile extends Backend
{
    protected $noNeedRight = 'update';

    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminLog();
    }

    public function index()
    {
        $where = new Where();
        $where['admin_id'] = $this->auth->id;

        $list = $this->model->where($where)->order("id", "desc")->paginate(20);

        $this->assign('list', $list);
        return $this->fetch();
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if (isset($params['password']) && !empty($params['password'])) {
                if (!Validate::is($params['password'], "/^[\S]{6,16}$/")) {
                    $this->error('密码长度必须在6-16位之间，不能包含空格');
                }
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
            }

            if ($params) {
                $admin = Admin::get($this->auth->id);
                $admin->save($params);
                $this->success('个人资料更新成功');
            }
            $this->error('没有数据需要更新!');
        }
        return;
    }
}