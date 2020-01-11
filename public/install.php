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
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
// ini_set('display_errors', '1');
// 定义目录分隔符
define('DS', DIRECTORY_SEPARATOR);

// 定义根目录
define('ROOT_PATH', __DIR__ . DS . '..' . DS);

// 定义应用目录
define('APP_PATH', ROOT_PATH . 'application' . DS);

// 安装包目录
define('INSTALL_PATH', ROOT_PATH . 'extend' . DS . 'install' . DS);

// 判断文件或目录是否有写的权限
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR == '/' AND @ ini_get("safe_mode") == false) {
        return is_writable($file);
    }
    if (!is_file($file) OR ($fp = @fopen($file, "r+")) === false) {
        return false;
    }

    fclose($fp);
    return true;
}

$sitename = "LanruAdmin";

// 检测目录是否存在
$checkDirs = [
    'thinkphp',
    'vendor',
    'public' . DS . 'assets' . DS . 'libs'
];
//缓存目录
$runtimeDir = APP_PATH . 'runtime';

//错误信息
$errInfo = '';

//数据库配置文件
$dbConfigFile = ROOT_PATH . 'config' . DS . 'database.php';
// 锁定的文件
$lockFile = INSTALL_PATH . 'install.lock';

if (is_file($lockFile)) {
    $errInfo = "当前已经安装{$sitename}，如果需要重新安装，请手动移除extend/install/install.lock文件";
} else {
    if (version_compare(PHP_VERSION, '7.0', '<')) {
        $errInfo = "当前版本(" . PHP_VERSION . ")过低，请使用PHP7.0以上版本";
    } else {
        if (!extension_loaded("PDO")) {
            $errInfo = "当前未开启PDO，无法进行安装";
        } else {
            if (!is_really_writable($dbConfigFile)) {
                $open_basedir = ini_get('open_basedir');
                if ($open_basedir) {
                    $dirArr = explode(PATH_SEPARATOR, $open_basedir);
                    if ($dirArr && in_array(__DIR__, $dirArr)) {
                        $errInfo = '当前服务器因配置了open_basedir，导致无法读取父目录<br>';
                    }
                }
                if (!$errInfo) {
                    $errInfo = '当前权限不足，无法写入配置文件config/database.php<br>';
                }
            } else {
                $dirArr = [];
                foreach ($checkDirs as $k => $v) {
                    if (!is_dir(ROOT_PATH . $v)) {
                        $errInfo = '当前代码仅包含核心代码，请前往官网下载完整包或资源包覆盖后再尝试安装。';
                        break;
                    }
                }
            }

        }
    }
}

//后台入口文件
$adminFile = ROOT_PATH . 'public' . DS . 'admin.php';

// 当前是POST请求
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($errInfo) {
        echo $errInfo;
        exit;
    }
    $err = '';
    $mysqlHostname = isset($_POST['mysqlHost']) ? $_POST['mysqlHost'] : '127.0.0.1';
    $mysqlHostport = isset($_POST['mysqlHostport']) ? $_POST['mysqlHostport'] : 3306;
    $hostArr = explode(':', $mysqlHostname);
    if (count($hostArr) > 1) {
        $mysqlHostname = $hostArr[0];
        $mysqlHostport = $hostArr[1];
    }
    $mysqlUsername = isset($_POST['mysqlUsername']) ? $_POST['mysqlUsername'] : 'root';
    $mysqlPassword = isset($_POST['mysqlPassword']) ? $_POST['mysqlPassword'] : '';
    $mysqlDatabase = isset($_POST['mysqlDatabase']) ? $_POST['mysqlDatabase'] : 'lanruadmin';
    $mysqlPrefix = isset($_POST['mysqlPrefix']) ? $_POST['mysqlPrefix'] : 'lan_';
    $adminUsername = isset($_POST['adminUsername']) ? $_POST['adminUsername'] : 'admin';
    $adminPassword = isset($_POST['adminPassword']) ? $_POST['adminPassword'] : '123456';
    $adminPasswordConfirmation = isset($_POST['adminPasswordConfirmation']) ? $_POST['adminPasswordConfirmation'] : '123456';
    $adminEmail = isset($_POST['adminEmail']) ? $_POST['adminEmail'] : 'admin@admin.com';

    if (!preg_match("/^\w{3,12}$/", $adminUsername)) {
        echo "用户名只能由3-12位数字、字母、下划线组合";
        exit;
    }
    if (!preg_match("/^[\S]{6,16}$/", $adminPassword)) {
        echo "密码长度必须在6-16位之间，不能包含空格";
        exit;
    }
    if ($adminPassword !== $adminPasswordConfirmation) {
        echo "两次输入的密码不一致";
        exit;
    }

    try {
        //检测能否读取安装文件
        $sql = @file_get_contents(INSTALL_PATH . 'lanAdmin.sql');
        if (!$sql) {
            throw new Exception("无法读取extend/install/lanAdmin.sql文件，请检查是否有读权限");
        }
        $sql = str_replace("`lan_", "`{$mysqlPrefix}", $sql);
        $pdo = new PDO("mysql:host={$mysqlHostname};port={$mysqlHostport}", $mysqlUsername, $mysqlPassword, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ));

        //检测是否支持innodb存储引擎
        $pdoStatement = $pdo->query("SHOW VARIABLES LIKE 'innodb_version'");
        $result = $pdoStatement->fetch();
        if (!$result) {
            throw new Exception("当前数据库不支持innodb存储引擎，请开启后再重新尝试安装");
        }

        $pdo->query("CREATE DATABASE IF NOT EXISTS `{$mysqlDatabase}` CHARACTER SET utf8 COLLATE utf8_general_ci;");

        $pdo->query("USE `{$mysqlDatabase}`");

        $pdo->exec($sql);

        $config = @file_get_contents($dbConfigFile);
        $callback = function ($matches) use ($mysqlHostname, $mysqlHostport, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlPrefix) {
            $field = ucfirst($matches[1]);
            $replace = ${"mysql{$field}"};
            if ($matches[1] == 'hostport' && $mysqlHostport == 3306) {
                $replace = '';
            }
            return "'{$matches[1]}'{$matches[2]}=>{$matches[3]}Env::get('database.{$matches[1]}', '{$replace}'),";
        };
        $config = preg_replace_callback("/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)Env::get\((.*)\)\,/", $callback, $config);

        //检测能否成功写入数据库配置
        $result = @file_put_contents($dbConfigFile, $config);
        if (!$result) {
            throw new Exception("无法写入数据库信息到config/database.php文件，请检查是否有写权限");
        }

        //检测能否成功写入lock文件
        $result = @file_put_contents($lockFile, 1);
        if (!$result) {
            throw new Exception("无法写入安装锁定到extend/install/install.lock文件，请检查是否有写权限");
        }

        $newSalt = substr(md5(uniqid(true)), 0, 6);
        $newPassword = md5(md5($adminPassword) . $newSalt);
        $pdo->query("UPDATE {$mysqlPrefix}admin SET name = '{$adminUsername}', password = '{$newPassword}', salt = '{$newSalt}' WHERE name = 'admin'");

        $adminName = '';
        if (is_file($adminFile)) {
            $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $adminName = substr(str_shuffle(str_repeat($x, ceil(10 / strlen($x)))), 1, 10) . '.php';
            rename($adminFile, ROOT_PATH . 'public' . DS . $adminName);
        }
        echo "success|{$adminName}";
    } catch (PDOException $e) {
        $err = $e->getMessage();
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
    echo $err;
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>安装<?php echo $sitename; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
    <meta name="renderer" content="webkit">

    <style>
        body {
            background: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body, input, button {
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, 'Microsoft Yahei', Arial, sans-serif;
            font-size: 14px;
            color: #7E96B3;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        a {
            color: #18bc9c;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 28px;
            font-weight: normal;
            color: #3C5675;
            margin-bottom: 0;
            margin-top: 0;
        }

        form {
            margin-top: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group .form-field:first-child input {
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .form-group .form-field:last-child input {
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .form-field input {
            background: #EDF2F7;
            margin: 0 0 1px;
            border: 2px solid transparent;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            width: 100%;
            padding: 15px 15px 15px 180px;
            box-sizing: border-box;
        }

        .form-field input:focus {
            border-color: #18bc9c;
            background: #fff;
            color: #444;
            outline: none;
        }

        .form-field label {
            float: left;
            width: 160px;
            text-align: right;
            margin-right: -160px;
            position: relative;
            margin-top: 18px;
            font-size: 14px;
            pointer-events: none;
            opacity: 0.7;
        }

        button, .btn {
            background: #3C5675;
            color: #fff;
            border: 0;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            padding: 15px 30px;
            -webkit-appearance: none;
        }

        button[disabled] {
            opacity: 0.5;
        }

        .form-buttons {
            height: 52px;
            line-height: 52px;
        }

        .form-buttons .btn {
            margin-right: 5px;
        }

        #error, .error, #success, .success, #warmtips, .warmtips {
            background: #D83E3E;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #success {
            background: #3C5675;
        }

        #error a, .error a {
            color: white;
            text-decoration: underline;
        }

        #warmtips {
            background: #ffcdcd;
            font-size: 14px;
            color: #e74c3c;
        }

        #warmtips a {
            background: #ffffff7a;
            display: block;
            height: 30px;
            line-height: 30px;
            margin-top: 10px;
            color: #e21a1a;
            border-radius: 3px;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="150px" height="96px" viewBox="0 0 568 100" enable-background="new 0 0 568 100" xml:space="preserve">  <image id="image0" width="568" height="100" x="0" y="0"                                                     href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAjgAAABkCAMAAAC8eLphAAAABGdBTUEAALGPC/xhBQAAACBjSFJN
AAB6JQAAgIMAAPn/AACA6QAAdTAAAOpgAAA6mAAAF2+SX8VGAAAAmVBMVEX///8RYt4RYt4RYt4R
Yt4RYt4RYt7/kBz/kBwRYt4RYt4RYt4RYt4RYt4RYt7/kBz/kBwRYt4RYt4RYt4RYt4RYt4RYt4R
Yt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt4RYt7/
kBwRYt4RYt4RYt4RYt4RYt4RYt7/kBz///98E//NAAAAMHRSTlMAEEBXcDwwMEB3TCDowKBgwIAz
VWZg0CKZRLuq4PBQEd2QsO7MiPHvvPkg/P5k2NReWQiGAAAAAWJLR0QAiAUdSAAAAAlwSFlzAAAL
EgAACxIB0t1+/AAAAAd0SU1FB+QBCBMKJGFZ4xkAAAn/SURBVHja7Z15e+I2EMYhS9m6lCwkhCuH
TSDdnlv3+3+5JsTCOmZGMz7hYd6/YqNbv4wljSwPBiqVSqVSqVQqlUqlUqlUKpVKpVKpVCqVSqVS
qVQqlUqlUqlUKpVKJdHwBlA7WX0ZBfqJG3d8g+ozwNefWfpq19opSjIa/XLD0AQuYIVW7KzhW9FN
DqiVnMZARr9yI09zVLfHAN/+Y2lG1JqpOVjACq1YreHv7qG7i7tW+oxQd+CAff9QJ3IFcmZErbla
QkanI3BW6zwJ7262+SYW87HI4sjdHbuyaHqdgQP31RMzNgWOhJwZVRiunl8aoUAe5R2bPARntX2/
GwMntbNIswsCB+l6pskhwRGQMyNqLdCuAQrEUdLkGMYD5whTHJxFkcPjx8VjzhaaYFfgvCAFex6y
otPg8MmZEbWW6NUvdgvg3G1d7T/DZODdg3v3zc9sXeSweP87EVS0d3CesJLNWdEj4LDJaQqcfOmR
0wI4mxrF2/qZFXzlqWSAk/cPzgNaMp7JiYHDJacxcHxyKrRiLEqT4NwX9w+iAU7eOzjDJ7xoLJMT
BYdJTnPgeORUaMVYlCbBMU+qRDTAyXsHZ04UjWVy4uDwyGkQnHx6QeCYJ9VqkG5CGayAn/oFZ/gc
7/Ha4LDIaRIcZ2513uCYYc0BLokZLUv6tBNw5nQtJ/EUOOBwyGkUnNxazzlvcN6K229wSc4VnAmr
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" />
</svg>
    </h1>
    <h2>安装 <?php echo $sitename; ?></h2>
    <div>


        <form method="post">
            <?php if ($errInfo): ?>
                <div class="error">
                    <?php echo $errInfo; ?>
                </div>
            <?php endif; ?>
            <div id="error" style="display:none"></div>
            <div id="success" style="display:none"></div>
            <div id="warmtips" style="display:none"></div>

            <div class="form-group">
                <div class="form-field">
                    <label>MySQL 数据库地址</label>
                    <input type="text" name="mysqlHost" value="127.0.0.1" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 数据库名</label>
                    <input type="text" name="mysqlDatabase" value="lanruadmin" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 用户名</label>
                    <input type="text" name="mysqlUsername" value="root" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 密码</label>
                    <input type="password" name="mysqlPassword">
                </div>

                <div class="form-field">
                    <label>MySQL 数据表前缀</label>
                    <input type="text" name="mysqlPrefix" value="lan_">
                </div>

                <div class="form-field">
                    <label>MySQL 端口号</label>
                    <input type="number" name="mysqlHostport" value="3306">
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label>管理者用户名</label>
                    <input name="adminUsername" value="admin" required=""/>
                </div>

                <div class="form-field">
                    <label>管理者密码</label>
                    <input type="password" name="adminPassword" required="">
                </div>

                <div class="form-field">
                    <label>重复密码</label>
                    <input type="password" name="adminPasswordConfirmation" required="">
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" <?php echo $errInfo ? 'disabled' : '' ?>>点击安装</button>
            </div>
        </form>

        <!-- jQuery -->
        <script src="https://cdn.staticfile.org/jquery/2.1.4/jquery.min.js"></script>

        <script>
            $(function () {
                $('form :input:first').select();

                $('form').on('submit', function (e) {
                    e.preventDefault();
                    var form = this;
                    var $button = $(this).find('button')
                        .text('安装中...')
                        .prop('disabled', true);

                    $.post('', $(this).serialize())
                        .done(function (ret) {
                            if (ret.substr(0, 7) === 'success') {
                                var retArr = ret.split(/\|/);
                                $('#error').hide();
                                $(".form-group", form).remove();
                                $button.remove();
                                $("#success").text("安装成功！开始你的<?php echo $sitename; ?>之旅吧！").show();

                                $buttons = $(".form-buttons", form);
                                $('<a class="btn" href="./">访问首页</a>').appendTo($buttons);
                                if (typeof retArr[1] !== 'undefined' && retArr[1] !== '') {
                                    var url = location.href.replace(/install\.php/, retArr[1]);
                                    $("#warmtips").html('温馨提示：请将以下后台登录入口添加到你的收藏夹，为了你的安全，不要泄漏或发送给他人！如有泄漏请及时修改！<a href="' + url + '">' + url + '</a>').show();
                                    $('<a class="btn" href="' + url + '" id="btn-admin" style="background:#18bc9c">访问后台</a>').appendTo($buttons);
                                }
                                localStorage.setItem("fastep", "installed");
                            } else {
                                $('#error').show().text(ret);
                                $button.prop('disabled', false).text('点击安装');
                                $("html,body").animate({
                                    scrollTop: 0
                                }, 500);
                            }
                        })
                        .fail(function (data) {
                            $('#error').show().text('发生错误:\n\n' + data.responseText);
                            $button.prop('disabled', false).text('点击安装');
                            $("html,body").animate({
                                scrollTop: 0
                            }, 500);
                        });

                    return false;
                });
            });
        </script>
    </div>
</div>
</body>
</html>
