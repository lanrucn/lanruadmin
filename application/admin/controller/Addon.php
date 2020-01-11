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

namespace app\admin\controller;


use app\common\controller\Backend;
use think\addons\AddonException;
use think\addons\Service;
use think\Exception;
use think\facade\App;
use think\facade\Cache;
use think\facade\Config;

class Addon extends Backend
{
    public function initialize()
    {
        parent::initialize();
        if (!$this->auth->isSuperAdmin() && in_array($this->request->action(), ['install', 'uninstall', 'local', 'upgrade'])) {
            return $this->error('无权限');
        }
    }

    public function index()
    {
        $addons = get_addon_list();
        foreach ($addons as $k => &$v) {
            $config = get_addon_config($v['name']);
            $v['config'] = $config ? 1 : 0;
            $v['url'] = str_replace($this->request->server('SCRIPT_NAME'), '', $v['url']);
        }
        $this->assign('addons', $addons);
        return $this->fetch();
    }

    //本地安装
    public function local()
    {
        Config::set('default_return_type', 'json');

        $file = $this->request->file('file');
        $addonTmpDir = App::getRuntimePath() . 'addons' . DS;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }

        $info = $file->rule('uniqid')->validate(['size' => 10240000, 'ext' => 'zip'])->move($addonTmpDir);

        if ($info) {
            $tmpName = substr($info->getFilename(), 0, stripos($info->getFilename(), '.'));
            $tmpAddonDir = ADDON_PATH . $tmpName . DS;
            $tmpFile = $addonTmpDir . $info->getSaveName();

            try{

                Service::unzip($tmpName);
                unset($info);
                @unlink($tmpFile);
                $infoFile = $tmpAddonDir . 'info.ini';
                if (!is_file($infoFile)) {
                    throw new Exception('插件配置文件未找到');
                }

                $config = Config::parse($infoFile, '', $tmpName);

                $name = isset($config['name']) ? $config['name'] : '';
                if (!$name) {
                    throw new Exception('插件配置信息不正确');
                }
                if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
                    throw new Exception('插件名称不正确');
                }

                $newAddonDir = ADDON_PATH . $name . DS;
                if (is_dir($newAddonDir)) {
                    throw new Exception('上传的插件已经存在');
                }

                //重命名插件文件夹
                rename($tmpAddonDir, $newAddonDir);
                try {
                    //默认禁用该插件
                    $info = get_addon_info($name);
                    if (isset($info['state'])&&$info['state']) {
                        $info['state'] = 0;
                        set_addon_info($name, $info);
                    }

                    //执行插件的安装方法
                    $class = get_addon_class($name);
                    if (class_exists($class)) {
                        $addon = new $class();
                        $addon->install();
                    }

                    //导入SQL
                    Service::importsql($name);

                    $info['config'] = get_addon_config($name) ? 1 : 0;
                    $this->success('插件安装成功！清除浏览器缓存和框架缓存后生效！', null, ['addon' => $info]);
                } catch (Exception $e) {
                    @rmdirs($newAddonDir);
                    throw new Exception($e->getMessage());
                }


            } catch (Exception $exception) {
                return $this->error($exception->getMessage());
            }

        } else {
            // 上传失败获取错误信息
            return $this->error($file->getError());
        }

    }

    //配置
    public function config($name = null)
    {
        $name = $name ? $name : $this->request->get("name");
        if (!$name) {
            return $this->error("插件名称参数不能为空");
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error('插件名称不正确');
        }
        if (!is_dir(ADDON_PATH . $name)) {
            $this->error('插件目录未找到');
        }
        $info = get_addon_info($name);
        $config = get_addon_fullconfig($name);
        if (!$info) {
            $this->error('插件未找到');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$v['name']])) {
                        if ($v['type'] == 'array') {
                            $params[$v['name']] = is_array($params[$v['name']]) ? $params[$v['name']] : (array)json_decode($params[$v['name']], true);
                            $value = $params[$v['name']];
                        } else {
                            $value = is_array($params[$v['name']]) ? implode(',', $params[$v['name']]) : $params[$v['name']];
                        }
                        $v['value'] = $value;
                    }
                }
                try {
                    //更新配置文件
                    set_addon_fullconfig($name, $config);
                    Service::refresh();
                    return $this->success();
                } catch (Exception $e) {
                    return $this->error($e->getMessage());
                }
            }
            return $this->error('参数不得为空');
        }
        $tips = [];
        foreach ($config as $index => &$item) {
            if ($item['name'] == '__tips__') {
                $tips = $item;
                unset($config[$index]);
            }
        }
        $this->assign("addon", ['info' => $info, 'config' => $config, 'tips' => $tips]);
        return $this->fetch();
    }

    /**
     * 禁用启用
     */
    public function state()
    {
        $name = $this->request->post("name");
        $action = $this->request->post("action");
        $force = (int)$this->request->post("force");
        if (!$name) {
            return $this->error('插件参数有误...');
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            return $this->error('插件名称有误...');
        }
        try {
            $action = $action == 'enable' ? $action : 'disable';
            //调用启用、禁用的方法
            Service::$action($name, $force);
            Cache::rm('__menu__');
            return $this->success('操作成功...');
        } catch (AddonException $e) {
            return $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            return $this->error(__($e->getMessage()));
        }
    }

    //卸载
    public function uninstall()
    {
        $name = $this->request->post("name");
        $force = (int)$this->request->post("force");
        if (!$name) {
            return $this->error("选择要删除的插件..");
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error("插件名称不对...");
        }
        try {
            Service::uninstall($name, $force);
            return $this->success("卸载成功...");
        } catch (AddonException $e) {
            return $this->result($e->getData(), $e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}