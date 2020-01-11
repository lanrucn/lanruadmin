/** License By http://www.lanru.cn/ **/
var Template = template;
var common = {
    init: function () {
        this.events.ispc();
        this.events.refresh();
        this.events.checkAll();

        //tooltip和popover
        if (!('ontouchstart' in document.documentElement)) {
            $('body').tooltip({selector: '[data-toggle="tooltip"]'});
        }
        $('body').popover({selector: '[data-toggle="popover"]'});

        //点击包含.btn-dialog的元素时弹出dialog
        $(document).on('click', '.btn-dialog,.dialogit', function (e) {
            var that = this;
            var options = $.extend({}, $(that).data() || {});
            var url = $(that).attr('data-href')
            var title = $(that).attr("title") || $(that).data("title") || $(that).data('original-title');

            if (typeof options.confirm !== 'undefined') {
                layer.confirm(options.confirm, function (index) {
                    common.api.open(url, title, options);
                    layer.close(index);
                });
            } else {
                common.api.open(url, title, options);
            }
            return false;
        });

        this.events.deleteAll();
        this.events.delete();

        $(document).on('click', '.btn-chooseone', function (e) {
            var multiple = common.api.query('multiple');
            multiple = multiple == 'true' ? true : false;
            common.api.close({url: $(this).data('url'), multiple: multiple});
        });
        // 选中多个
        $(document).on("click", ".btn-choose-multi", function () {
            var urlArr = new Array();
            $("input:checkbox[name^='ids']:checked").each(function(){
                urlArr.push($(this).data("url"));
            });

            var multiple = common.api.query('multiple');
            multiple = multiple == 'true' ? true : false;
            common.api.close({url: urlArr.join(","), multiple: multiple});
        });

        //搜索
        $(document).on('click', '.btn-search', function (e) {

            if ($(".search-table").hasClass("hide")) {
                $(".search-table").removeClass("hide");
            } else {
                $(".search-table").addClass("hide");
            }
        });

        if ($("form[role='form']").length > 0) {
            common.form.bindevent($("form[role='form']"));
        }

        if ($("form[role='search']").length > 0) {
            common.form.bindevent($("form[role='search']"));
        }

        $("a.md-delete-white").click(function () {
            $.ajax({
                url: $(this).attr("data-href"),
                dataType: 'json',
                cache: false,
                success: function (ret) {
                    if (ret.hasOwnProperty("code")) {
                        var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";
                        if (ret.code === 1) {
                            toastr.success(msg ? msg : '成功清理缓存');
                        } else {
                            toastr.error(msg ? msg : '清理失败...');
                        }
                    } else {
                        toastr.error('未知的数据格式');
                    }
                }, error: function () {
                    toastr.error('无网络...');
                }
            });
        });

    },
    data: {
        ismobile: false
    },
    api: {
        //打开一个弹出窗口
        open: function (url, title, options) {
            title = options && options.title ? options.title : (title ? title : "");
            url = url + (url.indexOf("?") > -1 ? "&" : "?") + "dialog=1";
            var area = [$(window).width() > 800 ? '800px' : '95%', $(window).height() > 600 ? '600px' : '95%'];
            options = $.extend({
                type: 2,
                title: title,
                shadeClose: true,
                shade: false,
                maxmin: true,
                moveOut: true,
                area: area,
                content: url,
                zIndex: layer.zIndex,
                success: function (layero, index) {
                    var that = this;
                    //存储callback事件
                    $(layero).data("callback", that.callback);
                    layer.setTop(layero);
                    try {
                        var frame = layer.getChildFrame('html', index);
                        var layerfooter = frame.find(".layer-footer");
                        common.api.layerfooter(layero, index, that);

                        //绑定事件
                        if (layerfooter.size() > 0) {
                            // 监听窗口内的元素及属性变化
                            // Firefox和Chrome早期版本中带有前缀
                            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
                            if (MutationObserver) {
                                // 选择目标节点
                                var target = layerfooter[0];
                                // 创建观察者对象
                                var observer = new MutationObserver(function (mutations) {
                                    common.api.layerfooter(layero, index, that);
                                    mutations.forEach(function (mutation) {
                                    });
                                });
                                // 配置观察选项:
                                var config = {attributes: true, childList: true, characterData: true, subtree: true}
                                // 传入目标节点和观察选项
                                observer.observe(target, config);
                                // 随后,你还可以停止观察
                                // observer.disconnect();
                            }
                        }
                    } catch (e) {

                    }
                    if ($(layero).height() > $(window).height()) {
                        //当弹出窗口大于浏览器可视高度时,重定位
                        layer.style(index, {
                            top: 0,
                            height: $(window).height()
                        });
                    }
                }
            }, options ? options : {});
            return layer.open(options);
        },
        //关闭窗口并回传数据
        close: function (data) {
            var index = parent.layer.getFrameIndex(window.name);
            var callback = parent.$("#layui-layer" + index).data("callback");
            //再执行关闭
            parent.layer.close(index);
            //再调用回传函数
            if (typeof callback === 'function') {
                callback.call(undefined, data);
            }
        },
        layerfooter: function (layero, index, that) {
            var frame = layer.getChildFrame('html', index);
            var layerfooter = frame.find(".layer-footer");
            if (layerfooter.size() > 0) {
                $(".layui-layer-footer", layero).remove();
                var footer = $("<div />").addClass('layui-layer-btn layui-layer-footer');
                footer.html(layerfooter.html());
                if ($(".row", footer).size() === 0) {
                    $(">", footer).wrapAll("<div class='row'></div>");
                }
                footer.insertAfter(layero.find('.layui-layer-content'));
                //绑定事件
                footer.on("click", ".btn", function () {
                    if ($(this).hasClass("disabled") || $(this).parent().hasClass("disabled")) {
                        return;
                    }
                    var index = footer.find('.btn').index(this);
                    $(".btn:eq(" + index + ")", layerfooter).trigger("click");
                });

                var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
                var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
                //重设iframe高度
                $("iframe", layero).height(layero.height() - titHeight - btnHeight);
            }
            //修复iOS下弹出窗口的高度和iOS下iframe无法滚动的BUG
            if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
                var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
                $("iframe", layero).parent().css("height", layero.height() - titHeight - btnHeight);
                $("iframe", layero).css("height", "100%");
            }
        },
        success: function (options, callback) {
            var type = typeof options === 'function';
            if (type) {
                callback = options;
            }
            return layer.msg('已处理...', $.extend({
                offset: 0, icon: 1
            }, type ? {} : options), callback);
        },
        error: function (options, callback) {
            var type = typeof options === 'function';
            if (type) {
                callback = options;
            }
            return layer.msg('失败啦...', $.extend({
                offset: 0, icon: 2
            }, type ? {} : options), callback);
        },
        //发送Ajax请求
        ajax: function (options, success, error) {
            options = typeof options === 'string' ? {url: options} : options;
            var index;
            if (typeof options.loading === 'undefined' || options.loading) {
                index = layer.load(options.loading || 0);
            }
            options = $.extend({
                type: "POST",
                dataType: "json",
                success: function (ret) {
                    index && layer.close(index);
                    ret = common.events.onAjaxResponse(ret);
                    if (ret.code === 1) {
                        common.events.onAjaxSuccess(ret, success);
                    } else {
                        common.events.onAjaxError(ret, error);
                    }
                },
                error: function (xhr) {
                    index && layer.close(index);
                    var ret = {code: xhr.status, msg: xhr.statusText, data: null};
                    common.events.onAjaxError(ret, error);
                }
            }, options);
            return $.ajax(options);
        },
        cdnurl: function (url) {
            return url;
        },
        //查询Url参数
        query: function (name, url) {
            if (!url) {
                url = window.location.href;
            }
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&/]" + name + "([=/]([^&#/?]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results)
                return null;
            if (!results[2])
                return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }
    },
    events: {
        //请求成功的回调
        onAjaxSuccess: function (ret, onAjaxSuccess) {
            var data = typeof ret.data !== 'undefined' ? ret.data : null;
            var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '操作成功!';

            if (typeof onAjaxSuccess === 'function') {
                var result = onAjaxSuccess.call(this, data, ret);
                if (result === false)
                    return;
            }
            toastr.success(msg);
        },
        //请求错误的回调
        onAjaxError: function (ret, onAjaxError) {
            var data = typeof ret.data !== 'undefined' ? ret.data : null;
            if (typeof onAjaxError === 'function') {
                var result = onAjaxError.call(this, data, ret);
                if (result === false) {
                    return;
                }
            }
            toastr.error(ret.msg);
        },
        //服务器响应数据后
        onAjaxResponse: function (response) {
            try {
                var ret = typeof response === 'object' ? response : JSON.parse(response);
                if (!ret.hasOwnProperty('code')) {
                    $.extend(ret, {code: -2, msg: response, data: null});
                }
            } catch (e) {
                var ret = {code: -1, msg: e.message, data: null};
            }
            return ret;
        },
        ispc: function () {
            var userAgentInfo = navigator.userAgent;
            var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
            var flag = true;
            for (var v = 0; v < Agents.length; v++) {
                if (userAgentInfo.indexOf(Agents[v]) > 0) {
                    flag = false;
                    break;
                }
            }
            common.data.ismobile = flag;
        },
        refresh: function () {
            $(".btn-refresh").click(function () {
                location.reload();
            });
        },
        checkAll: function () {
            $("[name='allIds'][type='checkbox']").click(function () {
                var parent = $(this).parent().parent().parent().parent();
                if ($(this).prop("checked")) {
                    $(parent).find("[name^='ids']").prop('checked', true);
                } else {
                    $(parent).find("[name^='ids']").prop('checked', false);
                }
            });
        },
        deleteAll: function () {
            $(".btn-delete-action").click(function () {
                var ids = [];
                $("input:checkbox[name^='ids']:checked").each(function(){
                    ids.push($(this).val());
                });

                if (ids.length <= 0) {
                    layer.msg('请选择要删除的项!');
                } else {
                    var url = $(this).attr('data-href');
                    layer.confirm('确认删除选中的' + ids.length +'项么?删除后不可恢复', {
                        btn: ['确认','取消'] //按钮
                    }, function(){

                        common.api.ajax({url: url, data: {id: ids.join("~")}}, function (data, ret) {
                            if (ret.code == 1) {
                                location.reload();
                                return false;
                            }
                            layer.closeAll();

                        });

                    }, function(){});
                }

            });
        },
        delete: function () {
            $(".btn-del").click(function (e) {
                e.preventDefault();
                var that = this;
                layer.confirm(
                    '确认删除此项么?删除后不可恢复',
                    {icon: 3, title: '提醒', shadeClose: true},
                    function (index) {
                        layer.closeAll();
                        common.api.ajax({url: $(that).attr('data-href')}, function (data, ret) {
                            if (ret.code == 1) {
                                location.reload();
                                return false;
                            }
                        });
                    }
                );

            });
        }
    },
    plupload: {
        list: {},
        config: {
            container: document.body,
            classname: '.plupload:not([initialized])',
            previewtpl: '<li class="col-xs-3"><a href="{{ fullurl }}" data-url="{{ url }}" target="_blank" class="thumbnail"><img src="{{ fullurl }}" onerror="this.src=\'/assets/icon/error.png\';this.onerror=null;" class="img-responsive"></a><a href="javascript:;" class="btn btn-danger btn-xs btn-trash"><i class="fa fa-trash"></i></a></li>',
        },
        events: {
            onInit: function (up) {
                //修复少数安卓浏览器无法上传图片的Bug
                var input = $("input[type=file]", $(up.settings.button).next());
                if (input && input.prop("accept") && input.prop("accept").match(/image\/jpeg/)) {
                    input.prop("accept", "image/jpg," + input.prop("accept"));
                }
            },
            //初始化完成
            onPostInit: function (up) {

            },
            //文件添加成功后
            onFileAdded: function (up, files) {
                var button = up.settings.button;
                $(button).data("bakup-html", $(button).html());
                var maxcount = $(button).data("maxcount");
                var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";
                maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
                if (maxcount > 0 && input_id) {
                    var inputObj = $("#" + input_id);
                    if (inputObj.size() > 0) {
                        var value = $.trim(inputObj.val());
                        var nums = value === '' ? 0 : value.split(/\,/).length;
                        var remains = maxcount - nums;
                        if (files.length > remains) {
                            for (var i = 0; i < files.length; i++) {
                                up.removeFile(files[i]);
                            }
                            toastr.error("可以上传" +remains+ "个文件");
                            return false;
                        }
                    }
                }
                //添加后立即上传
                setTimeout(function () {
                    up.start();
                }, 1);
            },
            //上传进行中的回调
            onUploadProgress: function (up, file) {

            },
            //上传之前的回调
            onBeforeUpload: function (up, file) {

            },
            //上传成功的回调
            onUploadSuccess: function (up, ret) {
                var button = up.settings.button;
                var onUploadSuccess = up.settings.onUploadSuccess;
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                //上传成功后回调
                if (button) {
                    //如果有文本框则填充
                    var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";
                    if (input_id) {
                        var urlArr = [];
                        var inputObj = $("#" + input_id);
                        if ($(button).data("multiple") && inputObj.val() !== "") {
                            urlArr.push(inputObj.val());
                        }
                        urlArr.push(data.url);
                        inputObj.val(urlArr.join(",")).trigger("change").trigger("validate");
                    }
                    //如果有回调函数
                    var onDomUploadSuccess = $(button).data("upload-success");
                    if (onDomUploadSuccess) {
                        if (typeof onDomUploadSuccess !== 'function' && typeof Upload.api.custom[onDomUploadSuccess] === 'function') {
                            onDomUploadSuccess = common.plupload.custom[onDomUploadSuccess];
                        }
                        if (typeof onDomUploadSuccess === 'function') {
                            var result = onDomUploadSuccess.call(button, data, ret);
                            if (result === false)
                                return;
                        }
                    }
                }

                if (typeof onUploadSuccess === 'function') {
                    var result = onUploadSuccess.call(button, data, ret);
                    if (result === false)
                        return;
                }
            },
            //上传错误的回调
            onUploadError: function (up, ret) {
                var button = up.settings.button;
                var onUploadError = up.settings.onUploadError;
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                if (button) {
                    var onDomUploadError = $(button).data("upload-error");
                    if (onDomUploadError) {
                        if (typeof onDomUploadError !== 'function' && typeof Upload.api.custom[onDomUploadError] === 'function') {
                            onDomUploadError = Upload.api.custom[onDomUploadError];
                        }
                        if (typeof onDomUploadError === 'function') {
                            var result = onDomUploadError.call(button, data, ret);
                            if (result === false)
                                return;
                        }
                    }
                }

                if (typeof onUploadError === 'function') {
                    var result = onUploadError.call(button, data, ret);
                    if (result === false) {
                        return;
                    }
                }
                toastr.error(ret.msg + "(code:" + ret.code + ")");
            },
            //服务器响应数据后
            onUploadResponse: function (response) {
                try {
                    var ret = typeof response === 'object' ? response : JSON.parse(response);
                    if (!ret.hasOwnProperty('code')) {
                        $.extend(ret, {code: -2, msg: response, data: null});
                    }
                } catch (e) {
                    var ret = {code: -1, msg: e.message, data: null};
                }
                return ret;
            },
            //上传全部结束后
            onUploadComplete: function (up, files) {
                var button = up.settings.button;
                var onUploadComplete = up.settings.onUploadComplete;
                if (button) {
                    var onDomUploadComplete = $(button).data("upload-complete");
                    if (onDomUploadComplete) {
                        if (typeof onDomUploadComplete !== 'function' && typeof Upload.api.custom[onDomUploadComplete] === 'function') {
                            onDomUploadComplete = Upload.api.custom[onDomUploadComplete];
                        }
                        if (typeof onDomUploadComplete === 'function') {
                            var result = onDomUploadComplete.call(button, files);
                            if (result === false)
                                return;
                        }
                    }
                }

                if (typeof onUploadComplete === 'function') {
                    var result = onUploadComplete.call(button, files);
                    if (result === false) {
                        return;
                    }
                }
            }
        },
        api: {
            //Plupload上传
            plupload: function (element, onUploadSuccess, onUploadError, onUploadComplete) {
                element = typeof element === 'undefined' ? common.plupload.config.classname : element;
                $(element, common.plupload.config.container).each(function () {
                    if ($(this).attr("initialized")) {
                        return true;
                    }
                    $(this).attr("initialized", true);
                    var that = this;
                    var id = $(this).prop("id");
                    var url = $(this).data("url");
                    var maxsize = $(this).data("maxsize");
                    var mimetype = $(this).data("mimetype");
                    var multipart = $(this).data("multipart");
                    var multiple = $(this).data("multiple");

                    //填充ID
                    var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
                    //预览ID
                    var preview_id = $(that).data("preview-id") ? $(that).data("preview-id") : "";

                    /*//最大可上传文件大小
                    maxsize = typeof maxsize !== "undefined" ? maxsize : Config.upload.maxsize;
                    //文件类型
                    mimetype = typeof mimetype !== "undefined" ? mimetype : Config.upload.mimetype;
                    //请求的表单参数
                    multipart = typeof multipart !== "undefined" ? multipart : Config.upload.multipart;
                    //是否支持批量上传
                    multiple = typeof multiple !== "undefined" ? multiple : Config.upload.multiple;*/
                    var mimetypeArr = new Array();
                    //支持后缀和Mimetype格式,以,分隔
                    if (mimetype && mimetype !== "*" && mimetype.indexOf("/") === -1) {
                        var tempArr = mimetype.split(',');
                        for (var i = 0; i < tempArr.length; i++) {
                            mimetypeArr.push({title: '文件', extensions: tempArr[i]});
                        }
                        mimetype = mimetypeArr;
                    }
                    //生成Plupload实例
                    common.plupload.list[id] = new plupload.Uploader({
                        runtimes: 'html5,flash,silverlight,html4',
                        multi_selection: multiple, //是否允许多选批量上传
                        browse_button: id, // 浏览按钮的ID
                        container: $(this).parent().get(0), //取按钮的上级元素
                        flash_swf_url: '/assets/libs/plupload/js/Moxie.swf',
                        silverlight_xap_url: '/assets/libs/plupload/js/Moxie.xap',
                        drop_element: [id, $(this).data("input-id")],
                        filters: {
                            max_file_size: maxsize,
                            mime_types: mimetype,
                        },
                        url: url,
                        multipart_params: $.isArray(multipart) ? {} : multipart,
                        init: {
                            PostInit: common.plupload.events.onPostInit,
                            FilesAdded: common.plupload.events.onFileAdded,
                            BeforeUpload: common.plupload.events.onBeforeUpload,
                            UploadProgress: function (up, file) {
                                var button = up.settings.button;
                                $(button).prop("disabled", true).html("<i class='fa fa-upload'></i> 上传" + file.percent + "%");
                                common.plupload.events.onUploadProgress(up, file);
                            },
                            FileUploaded: function (up, file, info) {
                                var button = up.settings.button;
                                //还原按钮文字及状态
                                $(button).prop("disabled", false).html($(button).data("bakup-html"));
                                var ret = common.plupload.events.onUploadResponse(info.response, info, up, file);
                                file.ret = ret;
                                if (ret.code === 1) {
                                    common.plupload.events.onUploadSuccess(up, ret, file);
                                } else {
                                    common.plupload.events.onUploadError(up, ret, file);
                                }
                            },
                            UploadComplete: common.plupload.events.onUploadComplete,
                            Error: function (up, err) {
                                var button = up.settings.button;
                                $(button).prop("disabled", false).html($(button).data("bakup-html"));
                                var ret = {code: err.code, msg: err.message, data: null};
                                common.plupload.events.onUploadError(up, ret);
                            }
                        },
                        onUploadSuccess: onUploadSuccess,
                        onUploadError: onUploadError,
                        onUploadComplete: onUploadComplete,
                        button: that
                    });

                    //拖动排序
                    if (preview_id && multiple) {
                        require(['dragsort'], function () {
                            $("#" + preview_id).dragsort({
                                dragSelector: "li a:not(.btn-trash)",
                                dragEnd: function () {
                                    $("#" + preview_id).trigger("fa.preview.change");
                                },
                                placeHolderTemplate: '<li class="col-xs-3"></li>'
                            });
                        });
                    }
                    //刷新隐藏textarea的值
                    var refresh = function (name) {
                        var data = {};
                        var textarea = $("textarea[name='" + name + "']");
                        var container = textarea.prev("ul");
                        $.each($("input,select,textarea", container).serializeArray(), function (i, j) {
                            var reg = /\[?(\w+)\]?\[(\w+)\]$/g;
                            var match = reg.exec(j.name);
                            if (!match)
                                return true;
                            if (!isNaN(match[2])) {
                                data[i] = j.value;
                            } else {
                                match[1] = "x" + parseInt(match[1]);
                                if (typeof data[match[1]] === 'undefined') {
                                    data[match[1]] = {};
                                }
                                data[match[1]][match[2]] = j.value;
                            }
                        });
                        var result = [];
                        $.each(data, function (i, j) {
                            result.push(j);
                        });
                        textarea.val(JSON.stringify(result));
                    };
                    if (preview_id && input_id) {
                        $(document.body).on("keyup change", "#" + input_id, function (e) {
                            var inputStr = $("#" + input_id).val();
                            var inputArr = inputStr.split(/\,/);
                            $("#" + preview_id).empty();
                            var tpl = $("#" + preview_id).data("template") ? $("#" + preview_id).data("template") : "";
                            var extend = $("#" + preview_id).next().is("textarea") ? $("#" + preview_id).next("textarea").val() : "{}";
                            var json = {};
                            try {
                                json = JSON.parse(extend);
                            } catch (e) {
                            }
                            $.each(inputArr, function (i, j) {
                                if (!j) {
                                    return true;
                                }
                                var data = {url: j, fullurl: common.api.cdnurl(j), data: $(that).data(), key: i, index: i, value: (json && typeof json[i] !== 'undefined' ? json[i] : null)};

                                var html = tpl ? template(tpl, data) : template.render(common.plupload.config.previewtpl, data);
                                $("#" + preview_id).append(html);
                            });
                        });
                        $("#" + input_id).trigger("change");
                    }
                    if (preview_id) {
                        //监听文本框改变事件
                        $("#" + preview_id).on('change keyup', "input,textarea,select", function () {
                            refresh($(this).closest("ul").data("name"));
                        });
                        // 监听事件
                        $(document.body).on("fa.preview.change", "#" + preview_id, function () {
                            var urlArr = [];
                            $("#" + preview_id + " [data-url]").each(function (i, j) {
                                urlArr.push($(this).data("url"));
                            });
                            if (input_id) {
                                $("#" + input_id).val(urlArr.join(","));
                            }
                            refresh($("#" + preview_id).data("name"));
                        });
                        // 移除按钮事件
                        $(document.body).on("click", "#" + preview_id + " .btn-trash", function () {
                            $(this).closest("li").remove();
                            $("#" + preview_id).trigger("fa.preview.change");
                        });
                    }
                    if (input_id) {
                        //粘贴上传
                        $("body").on('paste', "#" + input_id, function (event) {
                            var that = this;
                            var image, pasteEvent;
                            pasteEvent = event.originalEvent;
                            if (pasteEvent.clipboardData && pasteEvent.clipboardData.items) {
                                image = common.plupload.api.getImageFromClipboard(pasteEvent);
                                if (image) {
                                    event.preventDefault();
                                    var button = $(".plupload[data-input-id='" + $(that).attr("id") + "']");
                                    common.plupload.api.send(image, function (data) {
                                        var urlArr = [];
                                        if (button && button.data("multiple") && $(that).val() !== '') {
                                            urlArr.push($(that).val());
                                        }
                                        urlArr.push(data.url);
                                        $(that).val(urlArr.join(",")).trigger("change").trigger("validate");
                                    });
                                }
                            }
                        });
                        //拖拽上传
                        $("body").on('drop', "#" + input_id, function (event) {
                            var that = this;
                            var images, pasteEvent;
                            pasteEvent = event.originalEvent;
                            if (pasteEvent.dataTransfer && pasteEvent.dataTransfer.files) {
                                images = common.plupload.api.getImageFromDrop(pasteEvent);
                                if (images.length > 0) {
                                    event.preventDefault();
                                    var button = $(".plupload[data-input-id='" + $(that).attr("id") + "']");
                                    $.each(images, function (i, image) {
                                        common.plupload.api.send(image, function (data) {
                                            var urlArr = [];
                                            if (button && button.data("multiple") && $(that).val() !== '') {
                                                urlArr.push($(that).val());
                                            }
                                            urlArr.push(data.url);
                                            $(that).val(urlArr.join(",")).trigger("change").trigger("validate");
                                        });
                                    });
                                }
                            }
                        });
                    }
                    common.plupload.list[id].init();
                });
            },
            // AJAX异步上传
            send: function (file, onUploadSuccess, onUploadError, onUploadComplete) {
                var index = layer.msg("上传中", {offset: 't', time: 0});
                var id = Plupload.guid();
                var _onPostInit = common.plupload.events.onPostInit;
                common.plupload.events.onPostInit = function () {
                    // 当加载完成后添加文件并上传
                    common.plupload.list[id].addFile(file);
                    //Upload.list[id].start();
                };
                $('<button type="button" id="' + id + '" class="btn btn-danger hidden plupload" />').appendTo("body");
                $("#" + id).data("upload-complete", function (files) {
                    common.plupload.events.onPostInit = _onPostInit;
                    layer.close(index);
                });
                common.plupload.api.plupload("#" + id, onUploadSuccess, onUploadError, onUploadComplete);
            },
            custom: {
                //自定义上传完成回调
                afteruploadcallback: function (response) {
                    console.log(this, response);
                    alert("Custom Callback,Response URL:" + response.url);
                },
            },
            getImageFromClipboard: function (data) {
                var i, item;
                i = 0;
                while (i < data.clipboardData.items.length) {
                    item = data.clipboardData.items[i];
                    if (item.type.indexOf("image") !== -1) {
                        return item.getAsFile() || false;
                    }
                    i++;
                }
                return false;
            },
            getImageFromDrop: function (data) {
                var i, item, images;
                i = 0;
                images = [];
                while (i < data.dataTransfer.files.length) {
                    item = data.dataTransfer.files[i];
                    if (item.type.indexOf("image") !== -1) {
                        images.push(item);
                    }
                    i++;
                }
                return images;
            }
        }
    },
    form: {
        config: {
            fieldlisttpl: '<dd class="form-inline"><input type="text" name="{{ name }}[{{ index }}][key]" class="form-control" value="{{ row.key }}" size="10" /> <input type="text" name="{{ name }}[{{ index }}][value]" class="form-control" value="{{ row.value }}" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>'
        },
        bindevent: function (form, success, error, submit) {
            form = typeof form === 'object' ? form : $(form);

            var events = common.form.events;

            if ($(form).attr('role') != 'search' ) {
                events.validator(form, success, error, submit);
            }

            events.selectpicker(form);

            events.daterangepicker(form);

            events.selectpage(form);

            events.datetimepicker(form);

            events.plupload(form);

            events.faselect(form);

            events.fieldlist(form);

            events.iconselect(form);
        },
        events: {
            fieldlist: function (form) {
                //绑定fieldlist
                if ($(".fieldlist", form).size() > 0) {
                    //刷新隐藏textarea的值
                    var refresh = function (name) {
                        var data = {};
                        var textarea = $("textarea[name='" + name + "']", form);
                        var container = $(".fieldlist[data-name='" + name + "']");
                        var template = container.data("template");
                        $.each($("input,select,textarea", container).serializeArray(), function (i, j) {
                            var reg = /\[(\w+)\]\[(\w+)\]$/g;
                            var match = reg.exec(j.name);
                            if (!match)
                                return true;
                            match[1] = "x" + parseInt(match[1]);
                            if (typeof data[match[1]] == 'undefined') {
                                data[match[1]] = {};
                            }
                            data[match[1]][match[2]] = j.value;
                        });
                        var result = template ? [] : {};
                        $.each(data, function (i, j) {
                            if (j) {
                                if (!template) {
                                    if (j.key != '') {
                                        result[j.key] = j.value;
                                    }
                                } else {
                                    result.push(j);
                                }
                            }
                        });
                        textarea.val(JSON.stringify(result));
                    };
                    //监听文本框改变事件
                    $(document).on('change keyup changed', ".fieldlist input,.fieldlist textarea,.fieldlist select", function () {
                        refresh($(this).closest(".fieldlist").data("name"));
                    });
                    //追加控制
                    $(".fieldlist", form).on("click", ".btn-append,.append", function (e, row) {
                        var container = $(this).closest(".fieldlist");
                        var tagName = container.data("tag") || "dd";
                        var index = container.data("index");
                        var name = container.data("name");
                        var template = container.data("template");
                        var data = container.data();
                        index = index ? parseInt(index) : 0;
                        container.data("index", index + 1);
                        row = row ? row : {};
                        var vars = {index: index, name: name, data: data, row: row};
                        var html = template ? Template(template, vars) : Template.render(common.form.config.fieldlisttpl, vars);
                        $(html).insertBefore($(tagName + ":last", container));
                        $(this).trigger("fa.event.appendfieldlist", $(this).closest(tagName).prev());
                    });
                    //移除控制
                    $(".fieldlist", form).on("click", ".btn-remove", function () {
                        var container = $(this).closest(".fieldlist");
                        var tagName = container.data("tag") || "dd";
                        $(this).closest(tagName).remove();
                        refresh(container.data("name"));
                    });
                    //渲染数据&拖拽排序
                    $(".fieldlist", form).each(function () {
                        var container = this;
                        var tagName = $(this).data("tag") || "dd";
                        $(this).dragsort({
                            itemSelector: tagName,
                            dragSelector: ".btn-dragsort",
                            dragEnd: function () {
                                refresh($(this).closest(".fieldlist").data("name"));
                            },
                            placeHolderTemplate: $("<" + tagName + "/>")
                        });
                        var textarea = $("textarea[name='" + $(this).data("name") + "']", form);
                        if (textarea.val() == '') {
                            return true;
                        }
                        var template = $(this).data("template");
                        var json = {};
                        try {
                            json = JSON.parse(textarea.val());
                        } catch (e) {
                        }
                        $.each(json, function (i, j) {
                            $(".btn-append,.append", container).trigger('click', template ? j : {
                                key: i,
                                value: j
                            });
                        });
                    });
                }
            },
            iconselect: function(form) {
                if ($(".btn-search-icon", form).size() > 0) {
                    var iconlist = [];
                    var iconfunc = function () {
                        layer.open({
                            type: 1,
                            area: ['99%', '98%'], //宽高
                            content: template('chooseicontpl', {iconlist: iconlist})
                        });
                    };

                    $(document).on('click', ".btn-search-icon", function () {
                        if (iconlist.length == 0) {
                            $.get("/assets/libs/font-awesome/less/variables.less", function (ret) {
                                var exp = /fa-var-(.*):/ig;
                                var result;
                                while ((result = exp.exec(ret)) != null) {
                                    iconlist.push(result[1]);
                                }
                                iconfunc();
                            });
                        } else {
                            iconfunc();
                        }
                    });

                    $(document).on('click', '#chooseicon ul li', function () {
                        $("input[name='row[icon]']").val('fa fa-' + $(this).data("font"));
                        layer.closeAll();
                    });
                    $(document).on('keyup', 'input.js-icon-search', function () {
                        $("#chooseicon ul li").show();
                        if ($(this).val() != '') {
                            $("#chooseicon ul li:not([data-font*='" + $(this).val() + "'])").hide();
                        }
                    });

                }
            },
            faselect: function (form) {
                //绑定fachoose选择附件事件
                if ($(".fachoose", form).size() > 0) {
                    $(".fachoose", form).on('click', function () {
                        var that = this;
                        var multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                        var mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                        var admin_id = $(this).data("admin-id") ? $(this).data("admin-id") : '';
                        var user_id = $(this).data("user-id") ? $(this).data("user-id") : '';
                        var url = $(this).data("url");

                        common.api.open(url + "?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype + "&admin_id=" + admin_id + "&user_id=" + user_id, '选择', {
                            callback: function (data) {
                                var button = $("#" + $(that).attr("id"));
                                var maxcount = $(button).data("maxcount");
                                var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";
                                maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
                                if (input_id && data.multiple) {
                                    var urlArr = [];
                                    var inputObj = $("#" + input_id);
                                    var value = $.trim(inputObj.val());
                                    if (value !== "") {
                                        urlArr.push(inputObj.val());
                                    }
                                    urlArr.push(data.url)
                                    var result = urlArr.join(",");
                                    if (maxcount > 0) {
                                        var nums = value === '' ? 0 : value.split(/\,/).length;
                                        var files = data.url !== "" ? data.url.split(/\,/) : [];
                                        var remains = maxcount - nums;
                                        if (files.length > remains) {
                                            toastr.error('你最多还可以选择' + remains + '个文件');
                                            return false;
                                        }
                                    }
                                    inputObj.val(result).trigger("change").trigger("validate");
                                } else {
                                    $("#" + input_id).val(data.url).trigger("change").trigger("validate");
                                }
                            }
                        });
                        return false;
                    });
                }
            },
            validator: function (form, success, error, submit) {
                if (!form.is("form"))
                    return;
                //绑定表单事件
                form.validator($.extend({
                    validClass: 'has-success',
                    invalidClass: 'has-error',
                    bindClassTo: '.form-group',
                    formClass: 'n-default n-bootstrap',
                    msgClass: 'n-right',
                    stopOnError: true,
                    display: function (elem) {
                        return $(elem).closest('.form-group').find(".control-label").text().replace(/\:/, '');
                    },
                    dataFilter: function (data) {
                        if (data.code === 1) {
                            return data.msg ? { "ok": data.msg } : '';
                        } else {
                            return data.msg;
                        }
                    },
                    target: function (input) {
                        var target = $(input).data("target");
                        if (target && $(target).size() > 0) {
                            return $(target);
                        }
                        var $formitem = $(input).closest('.form-group'),
                            $msgbox = $formitem.find('span.msg-box');
                        if (!$msgbox.length) {
                            return [];
                        }
                        return $msgbox;
                    },
                    valid: function (ret) {
                        var that = this, submitBtn = $(".layer-footer [type=submit]", form);
                        that.holdSubmit(true);
                        submitBtn.addClass("disabled");
                        //验证通过提交表单
                        var submitResult = common.form.events.submit($(ret), function (data, ret) {
                            that.holdSubmit(false);
                            submitBtn.removeClass("disabled");
                            if (false === $(this).triggerHandler("success.form", [data, ret])) {
                                return false;
                            }
                            if (typeof success === 'function') {
                                if (false === success.call($(this), data, ret)) {
                                    return false;
                                }
                            }
                            //提示及关闭当前窗口
                            var msg = ret.hasOwnProperty("msg") && ret.msg !== "" ? ret.msg : '操作成功!';
                            parent.toastr.success(msg);
                            parent.$(".btn-refresh").trigger("click");
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            return false;
                        }, function (data, ret) {
                            that.holdSubmit(false);
                            if (false === $(this).triggerHandler("error.form", [data, ret])) {
                                return false;
                            }
                            submitBtn.removeClass("disabled");
                            if (typeof error === 'function') {
                                if (false === error.call($(this), data, ret)) {
                                    return false;
                                }
                            }
                        }, submit);
                        //如果提交失败则释放锁定
                        if (!submitResult) {
                            that.holdSubmit(false);
                            submitBtn.removeClass("disabled");
                        }
                        return false;
                    }
                }, form.data("validator-options") || {}));

                //移除提交按钮的disabled类
                $(".layer-footer [type=submit],.fixed-footer [type=submit],.normal-footer [type=submit]", form).removeClass("disabled");
            },
            selectpicker: function (form) {
                //绑定select元素事件
                if ($(".selectpicker", form).size() > 0) {
                    $('.selectpicker', form).selectpicker();
                    $(form).on("reset", function () {
                        setTimeout(function () {
                            $('.selectpicker').selectpicker('refresh').trigger("change");
                        }, 1);
                    });
                }
            },
            selectpage: function (form) {
                //绑定selectpage元素事件
                if ($(".selectpage", form).size() > 0) {
                    $('.selectpage', form).selectPage({
                        eAjaxSuccess: function (data) {
                            data.list = typeof data.rows !== 'undefined' ? data.rows : (typeof data.list !== 'undefined' ? data.list : []);
                            data.totalRow = typeof data.total !== 'undefined' ? data.total : (typeof data.totalRow !== 'undefined' ? data.totalRow : data.list.length);
                            return data;
                        }
                    });
                    //给隐藏的元素添加上validate验证触发事件
                    $(document).on("change", ".sp_hidden", function () {
                        $(this).trigger("validate");
                    });
                    $(document).on("change", ".sp_input", function () {
                        $(this).closest(".sp_container").find(".sp_hidden").trigger("change");
                    });
                    $(form).on("reset", function () {
                        setTimeout(function () {
                            $('.selectpage', form).selectPageClear();
                        }, 1);
                    });
                }
            },
            plupload: function (form) {
                //绑定plupload上传元素事件
                if ($(".plupload", form).size() > 0) {
                    common.plupload.api.plupload($(".plupload", form));
                }
            },
            daterangepicker: function (form) {
                //绑定日期时间元素事件
                if ($(".datetimerange", form).size() > 0) {
                    $(".datetimerange", form).each(function (element) {
                        var type = $(this).attr("data-date-format");
                        type = type ? type : "date";
                        laydate.render({
                            elem: this,
                            type: type,
                            range: true
                        });

                    });
                }
            },
            datetimepicker: function (form) {
                //绑定日期时间元素事件
                if ($(".datetimepicker", form).size() > 0) {
                    $(".datetimepicker", form).each(function (element) {
                        var type = $(this).attr("data-date-format");
                        type = type ? type : "datetime";
                        laydate.render({
                            elem: this,
                            type: type
                        });

                    });
                }
            },
            submit: function (form, success, error, submit) {
                if (form.size() === 0) {
                    toastr.error("表单未初始化完成,无法提交");
                    return false;
                }
                if (typeof submit === 'function') {
                    if (false === submit.call(form, success, error)) {
                        return false;
                    }
                }
                var type = form.attr("method") ? form.attr("method").toUpperCase() : 'GET';
                type = type && (type === 'GET' || type === 'POST') ? type : 'GET';
                url = form.attr("action");
                url = url ? url : location.href;
                //修复当存在多选项元素时提交的BUG
                var params = {};
                var multipleList = $("[name$='[]']", form);
                if (multipleList.size() > 0) {
                    var postFields = form.serializeArray().map(function (obj) {
                        return $(obj).prop("name");
                    });
                    $.each(multipleList, function (i, j) {
                        if (postFields.indexOf($(this).prop("name")) < 0) {
                            params[$(this).prop("name")] = '';
                        }
                    });
                }
                //调用Ajax请求方法
                common.api.ajax({
                    type: type,
                    url: url,
                    data: form.serialize() + (Object.keys(params).length > 0 ? '&' + $.param(params) : ''),
                    dataType: 'json',
                    complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']").val(token);
                        }
                    }
                }, function (data, ret) {
                    $('.form-group', form).removeClass('has-feedback has-success has-error');
                    if (data && typeof data === 'object') {
                        //刷新客户端token
                        if (typeof data.token !== 'undefined') {
                            $("input[name='__token__']").val(data.token);
                        }
                        //调用客户端事件
                        if (typeof data.callback !== 'undefined' && typeof data.callback === 'function') {
                            data.callback.call(form, data);
                        }
                    }
                    if (typeof success === 'function') {
                        if (false === success.call(form, data, ret)) {
                            return false;
                        }
                    }
                }, function (data, ret) {
                    if (data && typeof data === 'object' && typeof data.token !== 'undefined') {
                        $("input[name='__token__']").val(data.token);
                    }
                    if (typeof error === 'function') {
                        if (false === error.call(form, data, ret)) {
                            return false;
                        }
                    }
                });
                return true;
            }
        }
    }
};
$(function () {
    common.init();
});