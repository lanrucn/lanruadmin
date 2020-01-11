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

namespace app\api\controller;


use app\common\controller\Api;

class Index extends Api
{
    /**
     * 定义一个变量 用于apiGroup 因为不支持直接输入中文
     * @apiDefine test 测试
     */

    /**
     * @api {get} /Index/index
     * @apiName 登录
     * @apiGroup 首页
     * @apiPermission admin API的访问权限
     * @apiDescription  API的详细描述
     * @apiVersion 1.0.0
     *
     * @apiParam {string} username 用户名
     * @apiParam {string} password 密码
     *
     * @apiSuccess {Josn} json
     * {
     *  code: 0,
     *  msg: '',
     *  data: true,
     *  time: 100000
     * }
     * @apiSuccessExample Success-Response:
     * {
     *  code: 0,
     *  msg: '',
     *  data: true,
     *  time: 100000
     * }
     */
    public function index()
    {
        $this->success('请求成功');
    }
}