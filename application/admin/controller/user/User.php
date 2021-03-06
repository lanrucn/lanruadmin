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

namespace app\admin\controller\user;


use app\common\controller\Backend;
use app\common\model\UserGroup;
use app\common\model\UserRule;
use lanru\Random;
use think\Db;
use think\db\Where;
use think\Exception;

class User extends Backend
{
    protected $groupList;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub

        $this->model = new \app\common\model\User();

        $this->groupList = UserGroup::where('status', 1)->field('id, name')->all();
        $groupData = [];
        foreach ($this->groupList as $item) {
            $groupData[$item['id']] = $item['name'];
        }
        $this->assign('groupList', $groupData);

    }

    public function index()
    {
        $search = $this->request->param('search/a', []);
        $where = new Where();
        !empty($search['name']) and $where['user.name'] = ['like', '%' . $search['name'] .'%'];
        !empty($search['mobile']) and $where['user.mobile'] = ['like', '%' . $search['mobile'] .'%'];
        !empty($search['email']) and $where['user.email'] = ['like', '%' . $search['email'] .'%'];

        $list = $this->model->alias('user')->leftJoin('user_group', 'user.group_id = user_group.id')
                ->where($where)->field('user.*, user_group.name as group_txt')->order('user.id', 'desc')->paginate(15);

        $this->assign('list', $list)->assign('search', $search);
        return $this->fetch();
    }

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

                        $createData['salt'] = Random::alnum(6);
                        $createData['password'] = $this->model->makePassWord($createData['password'], $createData['salt']);

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

                        if (!empty($updata['password'])) {
                            $updata['salt'] = Random::alnum(6);
                            $updata['password'] = $this->model->makePassWord($updata['password'], $updata['salt']);
                         } else {
                            unset($updata['password']);
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
}