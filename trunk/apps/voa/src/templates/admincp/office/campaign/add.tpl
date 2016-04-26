{include file="$tpl_dir_base/header.tpl"}
<link rel="stylesheet" href="{$CSSDIR}askfor.css">
<style>
    body .datepicker-dropdown {
        position:absolute;
        z-index:2000 ! important;
    }

    #overtime {
        width: 150px;
        display: inline;
    }

    #time, #btime, #o_etime, #o_stime {
        height: 32px;
        width: 80px;
    }

    .gray {
        color: #999;
    }

    #prev_content div img {
        width: 267px;
    }
    .datepicker{ z-index:9999 !important}
    .effects-box {
        display:none;
    }
</style>
<div class="panel panel-default font12">
    <div class="panel-body">
        {if $step == 1}
            <form id="form" class="form-horizontal font12" role="form" method="post" action="javascript:;">
                <input type="hidden" name="formhash" value="{$formhash}"/>
                <input type="hidden" name="step" value="1">

                <div class="form-group">
                    <label class="col-sm-2 control-label">活动主题</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="subject" name="subject"
                               value="{$act['subject']|escape}"
                               maxlength="64"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">活动类型</label>


                    <div class="col-sm-10">
                        <select id="typeid" name="typeid" class="form-control form-small" data-width="auto">
                            {foreach $cats as $k => $v}
                                <option value="{$k}"{if $act['typeid'] == $k} selected="selected"{/if}>{$v}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">活动封面</label>
                    

                    <div class="col-sm-10">
                        <span class="gray">（在活动列表中显示的活动图片，图片建议尺寸：900像素 * 500像素）</span><br/><br/>
                        {cycp_upload
                        inputname='cover'
                        attachid = $act['cover']
                        showdelete=0
                        }
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">开始日期</label>

                    <div class="col-sm-10">
                        <input type="text" class="input-sm form-control" id="begintime" name="begintime"
                               style="width:150px;display:inline;" value="{$act._begintime|escape}"
                                />
                        <select id="btime" name="btime">
                            <option>全天</option>
                            {foreach $times as $k => $t}
                                <option value="{$t}"{if $t == $act._btime} selected{/if}>{$t}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">截止日期</label>

                    <div class="col-sm-10">
                        <input type="text" class="input-sm form-control" id="overtime" name="overtime"
                               value="{$act._overtime|escape}"/>
                        <select id="time" name="time">
                            <option>全天</option>
                            {foreach $times as $k => $t}
                                <option value="{$t}"{if $t == $act._time} selected{/if}>{$t}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">通知对象</label>

                    <div class="col-sm-10">
                        <p class="gray">（活动发出后，会第一时间推送消息的对象,若不选择会通知全部人员）</p>
                        {include
                        file="$tpl_dir_base/common_selector_member.tpl"
                        input_type='checkbox'
                        input_name='m_uids[]'
                        selector_box_id='users_container'
                        allow_member=true
                        allow_department=false
                        default_data=$users
                        }
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">活动地点</label>

                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="address" name="address"
                               value="{$act['address']|escape}"
                               maxlength="64"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">活动内容</label>

                    <div class="col-sm-10">
                        {$ueditor_output}
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10" style="text-align:center">
                        <input type="hidden" name="id" value="{$act['id']}"/>
                        <input type="hidden" name="action" value="{$action}"/>
                        {*{if $act.id && $act.is_push}*}
                        {*<button name="draft" type="submit" class="btn btn-primary">暂停活动</button>*}
                        {*{else}*}
                        {*<button name="draft" type="submit" class="btn btn-primary">保存草稿</button>*}
                        {*{/if}*}
                        &nbsp;&nbsp;
                        {*<button name="push" type="submit" class="btn btn-primary">发布</button>*}
                        <button name="push" class="btn btn-primary">下一步</button>
                    </div>
                </div>
            </form>
        {/if}
        {if  $step == 2}
            <form id="form" class="form-horizontal font12" role="form" method="post" action="javascript:;">
                <input type="hidden" name="step" value="2">

                <h2>接单详情</h2>

                <div class="form-group">
                    <label class="control-label col-sm-2" for="np">接单人数上限</label>

                    <div class="col-sm-3">
                        <input type="tel" class="form-control form-small" onkeyup="value=value.replace(/[^1234567890-]+/g,'')" value="{$act['nums']}" id="nums"
                               placeholder="接单人数上限,0表示无上限"
                               name="nums"
                               maxlength="5"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="np">最低影响力要求</label>

                    <div class="col-sm-3">
                        <input type="tel" class="form-control form-small" onkeyup="value=value.replace(/[^1234567890\.]+/g,'')" value="{$act['effect']}" id="effect"
                               placeholder="最低影响力要求，0表示无下限"
                               name="effect" maxlength="5"/>
                    </div>
                    <label class="control-label effects-box" for="np">高于此影响力指数的人有 <span id="effects" style="color:blue;">0</span> 人</label>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">抢单日期</label>

                    <div class="col-sm-10">
                        <input type="text" class="input-sm form-control" id="stime" name="stime"
                               style="width:150px;display:inline;" value="{$act._begintime|escape}"
                               required="required"/>
                        <select id="o_stime" name="o_stime">
                            <option>全天</option>
                            {foreach $times as $k => $t}
                                <option value="{$t}"{if $t == $act._btime} selected{/if}>{$t}</option>
                            {/foreach}
                        </select>
                        至
                        <input type="text" class="input-sm form-control" id="etime" name="etime"
                               style="width:150px;display:inline;" value="{$act._overtime|escape}"
                               required="required"/>
                        <select id="o_etime" name="o_etime">
                            <option>全天</option>
                            {foreach $times as $k => $t}
                                <option value="{$t}"{if $t == $act._time} selected{/if}>{$t}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10" style="text-align:center">
                        <input type="hidden" name="cid" value="{$acid}"/>
                        <input type="hidden" name="oid" value="{$act['o_id']}">
                        <input type="hidden" name="prev" id="prev" value="{$prev}">
                        <input type="hidden" name="endtime" value="{$endtime}">
                        {*<button name="push" type="submit" class="btn btn-primary">发布</button>*}
                        <a name="prev" id="back" class="btn btn-primary">
                            上一步
                        </a>
                        <button name="push" type="submit" class="btn btn-primary">下一步</button>
                    </div>
                </div>
            </form>
        {/if}

        {if $step == 3}
            <form id="form" class="form-horizontal font12" role="form" method="post" action="javascript:;">
                <input type="hidden" name="step" value="3">
                <div class="form-group">
                    <div class="row col-sm-8">
                        <label  class="control-label col-sm-2 text-danger askfor-label" for="id_title">姓名*</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row col-sm-8">
                        <label  class="control-label col-sm-2 text-danger askfor-label" for="id_title">手机号*</label>
                    </div>
                </div>
                <div class="form-group">
                    <hr>
                </div>
                <div id="customer_columns" class="clearfix" style="margin-left:60px;">
                    <div class="askfor-custom-header">
                        <ul class="askfor-custom-ul">
                            <li class="askfor-custom__li">
                                <span>类型</span>
                                <select class="askfor-custom-select js-custom-select">
                                    <option data-couple="false" data-type="text" value="1">文本</option>
                                    <option data-couple="false" data-type="number" value="2">数字</option>
                                </select>
                            </li>
                            <li class="askfor-custom__li">
                                <a href="javascript:void(0)" ask-clone="true"
                                   class="js-custom-field js-askfor-input askfor-custom__btn">添加字段</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <hr>
                </div>
                <div class="form-group col-sm-8">
                        <label class="control-label col-sm-2 askfor-label" for="id_title">选择预览人</label>
                        <div class="col-sm-3">
                        <p class="gray"></p>
                        {include
                        file="$tpl_dir_base/common_selector_member.tpl"
                        input_type='radio'
                        input_name='m_uids'
                        selector_box_id='users_container'
                        allow_member=true
                        allow_department=false
                        default_data=$deps
                        }
                        <input type="hidden" name="counts" value="0">
                        <button name="show" id="sendmsg" class="btn">预览</button>
                        <span id="dis" style="display: none;color: red;">请5分钟之后重新发送</span>
                        </div>
                    </div>

                <div class="form-group">

                    <div class="col-sm-10" style="text-align:center">
                        <input type="hidden" name="cid" value="{$acid}"/>
                        <input type="hidden" name="prev" id="prev" value="{$prev}">
                        {*<button name="push" type="submit" class="btn btn-primary">发布</button>*}
                        <button name="prev" type="submit" id="back" class="btn btn-primary">
                            上一步
                        </button>

                        <button name="push" style="width:100px;" class="btn btn-primary">发布</button>
                    </div>
                </div>

            </form>
        {/if}
    </div>
</div>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">预览</h4>
            </div>
            <div class="modal-body padding-sm">
                <h4 id="preview_title">标题</h4>

                <p class="text-default text-sm">2014-12-12 12:11 上海畅移</p>

                <hr>

                <div id="preview_content">内容</div>
            </div>
            <!-- / .modal-body -->
        </div>
        <!-- / .modal-content -->
    </div>
    <!-- / .modal-dialog -->
</div>
<!-- /.modal -->
<script>

    var customIndex = 0;
    function add_column() {

        var selector = $('#customer_columns .form-group');
        var length = selector.length
        index = customIndex;
        $.askfor.customClone(this, customIndex);
        var str = '';
        str += '<div class="form-group col-sm-8 js-custom-group  askfor-form-group">' +
        '<label class="control-label col-sm-2 text-danger askfor-label">自定义字段*</label>' +
        '<div class="col-sm-3 askfor-input-group">' +
        '	<input type="text" id="custom" class="form-control js-askfor-input form-small askfor-input" ask-index="' + index + '" name="cols[' + index + '][name]" placeholder="最多输入6个汉字" value="" maxlength="6" ask-visual="title" />' +
        '</div>' +
        '<div class="col-sm-3 askfor-input-group">' +
        '<input type="text" class="form-control form-small js-custom-read" disabled="disabled"/>' +
        '<input type="hidden" class="js-custom-hidden" name="cols[' + index + '][type]" value=""/>' +
        '</div>' +
        '<div class="col-sm-1 askfor-label-w10 askfor-p0">' +
        '	<input type="checkbox" name="cols[' + index + '][required]" value="0" onclick="this.value=(this.value==0)?1:0"/>' +
        '	<label>必填</label>' +
        '</div>' +
        '<div class="col-sm-1">' +
        '	<a href="javascript:void(0);" role="button" class="btn btn-default js-custom-del js-askfor-input" ask-del="' + index + '" ask-visual="title" onclick="delete_column(this)">删除</a>	' +
        '</div>' +
        '</div>';
        $('#customer_columns').append(str);
        ++customIndex;

    }
    //判断自定义字段是否为空
    function checkContent(obj){
    if(document.getElementById(obj).value.length==0){
        alert('自定义字段不能为空');
        return false;
        }
    }
    // 删除自定义字段
    function delete_column(obj) {
        function delFn() {
            $.askfor.custom_del = confirm("确认删除吗？");

            if ($.askfor.custom_del) {
                // $(obj).parents('.form-group').first().remove();
                $.askfor.__customDel(obj);
            }
        }

        setTimeout(delFn, 100);
    }
</script>
<script>
    var flag = true;
    $(function () {
        init.push(function () {
            var options2 = {
                todayBtn: "linked",
                orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
                startDate: new Date()
            };
            $('#overtime').datepicker(options2);
            $('#begintime').datepicker(options2);
            $('#stime').datepicker(options2);
            $('#etime').datepicker(options2);
        });

        {*影响力动态显示*}
        {literal}
        $('#effect').blur(function () {
            var effect = $(this).val();
            if (effect == 0) {
                $('.effects-box').hide();
            }
            if (effect !== '' && effect > 0) {
                $.ajax({
                    'type': 'POST',
                    'url': '/api/campaign/get/sale',
                    'data': {effect: effect},
                    'dataType': 'json',
                    success: function (data) {
                        if (data.errcode == 0) {
                            $('.effects-box').show();
                            $("#effects").html(data.result.total);
                        }
                    }
                })
            }
        })
        {/literal}

        // 开启预览
        $('#show').click(function () {
            $('#read').show();
        })

        // 取消预览
        $('#cancel').click(function(){
            $('#read').hide();
        })

        // 返回上一操作
        $('#back').click(function () {
            var prev = $("#prev").attr('value');

            location.href = prev;
        })

        {*发送预览 限定次数为3次*}
        {literal}
        $('#sendmsg').click(function () {
        	var i = 0;
            var data = $('#form').serializeArray();

            $.ajax({
                'type': 'POST',
                'url': '/api/campaign/post/sendmsg',
                'data': data,
                'dataType': 'json',
                success: function (data) {
                    if (data.errcode == 0) {
                        alert('发送成功');
                        $.ajax('/api/common/post/sendmsg');
                        i++;
                        $('input[name=counts]').attr('value', i);
                        //$('#dis').show();
                    } else {
                        alert(data.errmsg);
                    }
                }
            })
        })
        {/literal}

        $('button[name=push]').click(function () {
            if (flag == true) {
                flag = false;

                var data = $('#form').serializeArray();
                //根据按钮name,判断是发布还是草稿
                data.push({
                    name: 'is_push',
                    value: this.name == 'push' ? 1 : 0
                });
                for (k in data) {
                    if (data[k].name == 'content') {
                        if (data[k].value.length < 1) {
                            alert('活动内容不能为空');
                           return false;
                        }
                    }
                }
                $.post('/api/campaign/post/addcontent', data, function (data) {
                    if (data.errcode == 0) {
                        location.href = data.result.url;
                    } else {
                        alert(data.errmsg);
                    }
                    flag = true;
                }, 'json');
            } else {
                alert('请勿重新提交');
            }
        });
        //隐藏/显示预览图中的报名信息
        $('input[name=is_custom]').click(reg_img);
        reg_img();

        //预览处理
        setInterval(preview, 1000);
        $('#subject').change(function () {
            $('#prev_title').text(this.value);
        });
    });
    var content;
    //预览
    function preview() {
        var data = $('#form').serializeArray();
        for (k in data) {
            if (data[k].name == 'content') {
                var v = data[k].value;
            }
        }
        if (v != content) {
            content = v;
            $('#prev_content>div').html(v);
        }
    }
    //报名信息隐藏/显示
    function reg_img() {
        if ($('input[name=is_custom]:checked').val() == 1) {
            $('#reg_img').show();
        } else {
            $('#reg_img').hide();
        }
    }
</script>
<script type="text/javascript" src="{$JSDIR}askfor.js"></script>
{include file="$tpl_dir_base/footer.tpl"}
