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

namespace app\admin\controller\general;


use app\common\controller\Backend;
use think\Db;
use think\db\Where;
use think\Exception;
use think\facade\App;

class Attachment extends Backend
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\common\model\Attachment();
    }

    private function getList() {
        $list = [];
        $search = $this->request->post('search/a');
        $pagesize = 10;

        //查询条件
        $where = new Where();
        !empty($search['url']) and $where['url'] = ['like', '%' . $search['url'] . '%'];
        !empty($search['type']) and $where['type'] = ['like', '%' . $search['type'] . '%'];
        if (!empty($search['addtime'])) {
            list($begin, $end) = explode(" - ", $search['addtime']);
            $where['addtime'] = ['between', [strtotime($begin), strtotime($end)]];
        }

        $list = $this->model->where($where)->order('addtime desc')->paginate($pagesize);
        $this->assign('list', $list)->assign('search', $search);
    }

    public function index() {
        $this->getList();
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()){
            return $this->success();
        }

        return $this->fetch();
    }

    public function edit()
    {
        return $this->success();
    }

    /**
     * 选择附件
     */
    public function select()
    {
        $this->getList();
        return $this->fetch();
    }

    /**
     * 删除附件
     * @param array $ids
     */
    public function del()
    {
        $id = $this->request->param('id');
        if (empty($id)) return $this->error('请选择删除的项目!');

        if (stripos($id, '~') !== false) $id = explode("~", $id);

        try {
            Db::transaction(function () use ($id) {
                $list = $this->model->where($this->model->getPk(), $id)->select();

                foreach ($list as $item) {
                    $attachmentFile = App::getRootPath() . '/public' . $item['url'];
                    if (is_file($attachmentFile)) {
                        @unlink($attachmentFile);
                    }

                    $item->delete();
                }

            });
        } catch (Exception $ex) {
            return $this->error($ex->getMessage());
        }

        return $this->success('删除成功!');

    }

}