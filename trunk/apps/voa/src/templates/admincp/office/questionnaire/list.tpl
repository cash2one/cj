{include file="$tpl_dir_base/header.tpl"}
<style>
    #questionnaire_tpl span {
        cursor:pointer;
        padding:0 5px;
    }

    .spinner {
        margin: 0 auto;
        width: 150px;
        text-align: center;
    }

    .spinner > div {
        width: 30px;
        height: 30px;
        background-color: #0c79cf;

        border-radius: 100%;
        display: inline-block;
        -webkit-animation: bouncedelay 1.4s infinite ease-in-out;
        animation: bouncedelay 1.4s infinite ease-in-out;
        /* Prevent first frame from flickering when animation starts */
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .spinner .bounce1 {
        -webkit-animation-delay: -0.32s;
        animation-delay: -0.32s;
    }

    .spinner .bounce2 {
        -webkit-animation-delay: -0.16s;
        animation-delay: -0.16s;
    }

    @-webkit-keyframes bouncedelay {
        0%, 80%, 100% { -webkit-transform: scale(0.0) }
        40% { -webkit-transform: scale(1.0) }
    }

    @keyframes bouncedelay {
        0%, 80%, 100% {
            transform: scale(0.0);
            -webkit-transform: scale(0.0);
        } 40% {
              transform: scale(1.0);
              -webkit-transform: scale(1.0);
          }
    }
</style>
<div class="panel panel-default font12">
    <div class="panel-heading"><strong>搜索问卷</strong></div>
    <div class="panel-body" style="padding-bottom: 0">
        <form class="form-inline vcy-from-search" role="form" action="" >
            <input type="hidden" name="issearch" value="1"/>
            <div class="form-row m-b-20">
                <div class="form-group">
                    <script>
                        init.push(function () {
                            var options = {
                                todayBtn: "linked",
                                orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
                            }
                            $('#bs-datepicker-range').datepicker(options);
                            $('#bs-datepicker-range1').datepicker(options);
                        });
                    </script>
                    <label class="vcy-label-none" for="id_issuestart_date">发布日期：</label>

                    <div class="input-daterange input-group" id="bs-datepicker-range">
                        <input type="text" class="input-sm form-control" id="id_issuestart_date" name="issuestart"
                               placeholder="开始日期" value=""/>
                        <span class="input-group-addon">至</span>
                        <input type="text" class="input-sm form-control" id="id_issueend_date" name="issueend"
                               placeholder="结束日期" value=""/>
                    </div>

                    <label class="vcy-label-none" for="id_vo_status">状态：</label>
                    <select name="status" id="id_vo_status" class="form-control form-small" data-width="auto">
                        <option value="">全部</option>
                        <option value="2">草稿</option>
                        <option value="1">预发布</option>
                        <option value="3">进行中</option>
                        <option value="4" >已结束</option>
                    </select>

                </div>

            </div>
            <div class="form-row  m-b-20">
                <div class="form-group">
                    <label class="vcy-label-none" for="id_begin_date">结束日期：</label>

                    <div class="input-daterange input-group"
                         style="display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
                        <input type="text" class="input-sm form-control" id="id_begin_date" name="start"
                               placeholder="开始日期" value=""/>
                        <span class="input-group-addon">至</span>
                        <input type="text" class="input-sm form-control" id="id_end_date" name="end"
                               placeholder="结束日期" value=""/>
                    </div>
                    <label class="vcy-label-none" for="id_subject">关键字：</label>
                    <input type="text" class="form-control form-small" id="id_subject" name="title"
                           value="{$search_conds['subject']}" maxlength="54"/>
                    <span class="space"></span>
                    <label class="vcy-label-none" for="id_vo_cid">分类：</label>
                    <select name="cid" id="id_vo_cid" class="form-control form-small" data-width="auto">
                        <option value="">全部</option>

                    </select>
                </div>
                <span class="space"></span>
                <button  type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i
                            class="fa fa-search"></i> 搜索
                </button>
                <span class="space"></span>
                <button type="button" onclick="location.href='{$list_url}'" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 所有问卷</button>
            </div>
        </form>
    </div>
</div>

<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">
            记录列表
        </div>
    </div>
    <table class="table table-striped table-bordered table-hover font12">
        <colgroup>
            <col class="t-col-5"/>
            <col class="t-col-20"/>
            <col class="t-col-12"/>
            <col class="t-col-12"/>
            <col class="t-col-10"/>
            <col class="t-col-6"/>
            <col />
        </colgroup>
        <thead>
        <tr>
            <th class="text-left"><label class="checkbox">
                    <input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"/><span class="lbl">全选</span></label></th>
            <th class="text-left">标题</th>
            <th>发布时间</th>
            <th>结束时间</th>
            <th>状态</th>
            <th>参与人数</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody id="questionnaire_tpl">
        </tbody>
        <tfoot id="questionnaire_foot">
        </tfoot>
</div>

{literal}
    <script type="text/html" id="tpl_questionnaire_list">
        <%if (!jQuery.isEmptyObject(list)) {%>
            <%jQuery.each(list, function(key, item) {%>
                <tr>
                    <td class="text-left">
                        <label class="px-single">
                            <input type="checkbox" name="delete[]" class="px" value="<%=item.qu_id%>" /><span class="lbl"> </span>
                        </label>
                    </td>
                    <td class="text-left"><%=item.title%></td>
                    <td><%=item.created%></td>
                    <td><%=item.deadline%></td>
                    <td><%=item.status%></td>
                    <td><%=item.total%></td>
                    <td class="qu-handle">
                        <span class="export" rel="<%=item.qu_id%>"><i class="fa fa-angle-double-down"></i> 导出问卷</span>|
                        <span class="copy"  rel="<%=item.qu_id%>"><i class="fa fa-copy"></i> 快速复制</span>|
                        <span class="del_id" rel="<%=item.qu_id%>" ><i class="fa fa-trash-o"></i> 删除</span>|
                        <%if(item.status == '草稿'){%>
                            <span class="edit_list"  data-id="<%=item.qu_id%>"><i class="fa fa-edit"></i> 编辑</span>
                        <%}else if(item.status == '进行中'){%>
                            <span class="edit_list"  data-id="<%=item.qu_id%>"><i class="fa fa-edit"></i> 编辑</span>|
                            <span class="now_end" rel="<%=item.qu_id%>"><i class="fa fa-minus-square-o"></i> 结束</span>|
                            <span href="javascript:;" class="situation" data-id="<%=item.qu_id%>" ><i class="fa fa-file-text-o "></i> 填写情况</span>|
                            <span class="send_message" rel="<%=item.qu_id%>" ><i class="fa fa-bell-o"></i> 未填提醒</span>
                        <%}else if(item.status == '预发布'){%>
                            <span class="edit_list" data-id="<%=item.qu_id%>"><i class="fa fa-edit"></i> 编辑</span>
                            <span class="now_end" rel="<%=item.qu_id%>"><i class="fa fa-minus-square-o"></i> 结束</span>
                        <%}else{%>
                            <span href="javascript:;" class="situation" data-id="<%=item.qu_id%>" ><i class="fa fa-file-text-o"></i> 填写情况</span>
                        <%}%>
                    </td>
                </tr>
            <%});%>
        <% }else{ %>
            <tr>
                <td colspan="7" class="warning">
                    暂无信息
                </td>
            </tr>
        <%}%>
    </script>

    <script type="text/template" id="tpl_questionnaire_foot">

        <tr>
            <td colspan="2" class="text-left">
                <% if(!jQuery.isEmptyObject(multi)){ %>
                    <span class="btn btn-danger" id="del_button">批量删除</span>
                <% } %>
            </td>
            <td colspan="5" class="text-right">
                <% if(multi != null){ %>
                <%=multi%>
                <% } %>
            </td>
        </tr>

    </script>

    <script type="text/template" id="tpl_questionnaire_loading">

        <tr>
            <td colspan="7" class="text-center">
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </td>
        </tr>

    </script>
{/literal}
<script type="text/javascript" src="{$JSDIR}jquery.blockUI.min.js"></script>
<script>
    var situation_url = '{$situation_url}';
    var edit_url = '{$edit_url}';
    var export_url = '/Questionnaire/Apicp/Export/Naire?qu_id=';
    $(function(){
        $.ajaxSetup({ cache:false });
        var hrefs = window.location.href;//获取参数
        /*获取分类*/
        $.ajax({
            type:'get',
            url:'/Questionnaire/Apicp/Classify/List',
            dataType:'json',
            success:function(response){
                if(response.result.list){
                    var cid_tpl = '';
                    $.each( response.result.list, function(i, item){
                        cid_tpl += '<option value="'+ item.qc_id+'">'+item.name+'</option>';
                    })
                    $('#id_vo_cid').append(cid_tpl);
                }
                if(hrefs.indexOf('?') >= 0){
                    var ev = {
                            issuestart : get_querystring(hrefs, 'issuestart'),
                            issueend : get_querystring(hrefs, 'issueend'),
                            start : get_querystring(hrefs, 'start'),
                            end : get_querystring(hrefs, 'end'),
                            title : get_querystring(hrefs, 'title'),
                            cid : get_querystring(hrefs, 'cid'),
                            status : get_querystring(hrefs, 'status')
                        }
                    $('#id_issuestart_date').val(ev.issuestart);
                    $('#id_issueend_date').val(ev.issueend);
                    $('#id_begin_date').val(ev.start);
                    $('#id_end_date').val(ev.end);
                    $('#id_subject').val(ev.title);
                    $('#id_vo_cid').val(ev.cid);
                    $('#id_vo_status').val(ev.status);
                }
            }
        })
        Questionnaire_list();

        $('#questionnaire_foot').on('click', '#del_button', function() {
            $('#tmp_id').attr('val', '');
            $('#confirmModal').modal('show');
        });

        var questionnaire_tpl = $('#questionnaire_tpl');
        questionnaire_tpl.on('click','.del_id',function(){
            $('#tmp_id').attr('val', $(this).attr('rel'));
            $('#confirmModal').modal('show');
        });

        questionnaire_tpl.on('click','.now_end',function(){
            $('#end_tmp_id').val($(this).attr('rel'));
            $('#confirmendModal').modal('show');
        });

        questionnaire_tpl.on('click','.send_message',function(){
            $('#message_tmp_id').val($(this).attr('rel'));
            $('#message').modal('show');
        });

        /*问卷详情列表*/
        questionnaire_tpl.on('click','.situation',function(){
            window.location.href = situation_url + $(this).data('id');
        });


        /*问卷编辑*/
        questionnaire_tpl.on('click','.edit_list',function(){
            window.location.href = edit_url + $(this).data('id');
        });

        /*问卷复制*/
        questionnaire_tpl.on('click','.copy',function(){
            window.location.href = edit_url + $(this).attr('rel') + '&c=1';
        });

        /*问卷导出*/
        questionnaire_tpl.on('click','.export',function(){
            window.location.href = export_url + $(this).attr('rel');
        });

        questionnaire_tpl.on('mouseover', '.qu-handle span', function(){
            $(this).css('color','#1eafff');
        })
        questionnaire_tpl.on('mouseout', '.qu-handle span', function(){
            $(this).css('color', '');
        })
    })


    //ajax请求成员数据
    function Questionnaire_list(event) {
        $('#questionnaire_tpl').html(txTpl('tpl_questionnaire_loading'));
        $data = [];
        if(!event) {
            var hrefs = window.location.href;//获取参数
            if (hrefs.indexOf('?') >= 0) {
                event = {
                    data : {
                        issuestart : get_querystring(hrefs, 'issuestart'),
                        issueend : get_querystring(hrefs, 'issueend'),
                        start : get_querystring(hrefs, 'start'),
                        end : get_querystring(hrefs, 'end'),
                        title : get_querystring(hrefs, 'title'),
                        cid : get_querystring(hrefs, 'cid'),
                        page : get_querystring(hrefs, 'page'),
                        status : get_querystring(hrefs, 'status')
                    }
                }
            }
        }
        if(event){
            var $data = {
                cid : event.data.cid,
                issuestart : event.data.issuestart,
                issueend : event.data.issueend,
                status : event.data.status,
                start: event.data.start,
                end :event.data.end,
                title : event.data.title,
                page : event.data.page
            };
        }

        $.ajax({
            type : 'get',
            url:'/Questionnaire/Apicp/Questionnaire/list',
            data : $data,
            dataType : 'json',
            success : function(response) {

                $('#questionnaire_tpl').html(txTpl('tpl_questionnaire_list', response.result));
                $('#questionnaire_foot').html(txTpl('tpl_questionnaire_foot', response.result));
                // 绑定分页参数点击事件
                _multi('/Questionnaire/Apicp/Questionnaire/list', ['questionnaire_tpl', 'questionnaire_foot'], event);
            }
        });
    }

    //获取url中的qs参数
    function get_querystring(url, name) {

        var result = url.match(new RegExp("[\?\&]" + name+ "=([^\&]+)","i"));
        if(result == null || result.length < 1){
            return '';
        }
        return decodeURI(result[1]);
    }

    function getUrlArgStr(){
        var q=location.search.substr(1);
        var qs=q.split('&');
        var argStr='';
        if(qs){
            for(var i=0;i<qs.length;i++){
                if('page' == qs[i].substring(0,qs[i].indexOf('='))){
                    continue;
                }
                argStr+=qs[i].substring(0,qs[i].indexOf('='))+'='+decodeURI(qs[i].substring(qs[i].indexOf('=')+1))+'&';
            }
        }
        return argStr;
    }

    //显示页面遮罩层
    function blockUI() {
        var html = '<div class="loading-message"><img src="{$IMGDIR}loading-grey.gif" /></div>';

        $.blockUI({
            message: html,
            baseZ: 1100,
            css: {
                padding: "0",
                border: "0",
                backgroundColor: "none"
            },
            overlayCSS: {
                backgroundColor: "#000000",
                opacity: 0.1,
                cursor: "wait"
            }
        });
    }

    //关闭页面遮罩层
    function unblockUI() {
        $.unblockUI();
    }

    //结束当前
    function act_end() {
        var act_id = $('#end_tmp_id').val();
        if (act_id == '') {
            return false;
        }
        $.ajax({
            url: '/Questionnaire/Apicp/Questionnaire/questionnaireEnd',
            dataType: 'json',
            data: {
                qu_id: act_id
            },
            type: 'POST',
            success: function (result) {
                if (result.errcode != 0) {
                    alert(result.errmsg);
                    return false;
                }
                Questionnaire_list();

            },
            error: function () {
                alert('网络错误');
                return false;
            }
        });
    }



    //消息提醒
    function act_message() {
        var act_id = $('#message_tmp_id').val();
        if (act_id == '') {
            return false;
        }
        blockUI();
        $.ajax({
            url: '/Questionnaire/Apicp/Record/questionnaireSend',
            dataType: 'json',
            data: {
                qu_id: act_id
            },
            type: 'POST',
            success: function (result) {
                if (result.errcode != 0) {
                    alert(result.errmsg);
                    return false;
                }
                unblockUI();
                alert('消息推送成功');
                Questionnaire_list();

            },
            error: function () {
                alert('网络错误');
                return false;
            }
        });
    }

    // 删除记录操作
    function act_del() {

        var act_id = $('#tmp_id').attr('val');
        if (act_id == '') {
            act_id = [];
            var items = $('[name = "delete[]"]:checkbox:checked');

            for (var i = 0; i < items.length; i++) {
                // 如果i+1等于选项长度则取值后添加空字符串，否则为逗号
                act_id.push(items[i].value)
            }
            if(act_id == []){
                return false;
            }
        }

        $.ajax({
            url: '/Questionnaire/Apicp/Questionnaire/questionnaireDel',
            dataType: 'json',
            data: {
                qu_id: act_id
            },
            type: 'POST',
            success: function (result) {
                if (result.errcode != 0) {
                    alert(result.errmsg);
                    return false;
                }
                $('#delete-all').attr("checked",false);
                Questionnaire_list();

            },
            error: function () {
                alert('网络错误');
                return false;
            }
        });
    }

    // 绑定点击事件
    function _multi(url, id, data) {

        $('#' + id[0] + ' a').on('click', function () {
            var page = 'page';
            var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
            if (result == null || result.length < 1) {
                return false;
            }
            data.page = result[1]; // 页数
            // 获取列表数据
            Questionnaire_list(data)
            return false;
        });

        return true;
    }


</script>
<div class="modal modal-alert modal-danger fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-warning"></i>
            </div>
            <div class="modal-title"></div>
            <div class="modal-body">
                <span class=" text-info">删除后所有填写结果都会被删除,您确认删除该问券吗？</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-delete" onclick="act_del();" data-dismiss="modal">确定</button>
                <input type="hidden" name="delete-qc-id"/>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-alert modal-danger fade" id="confirmendModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-warning"></i>
            </div>
            <div class="modal-title"></div>
            <div class="modal-body">
                <input type="hidden" value="" id="end_tmp_id"/>
                <span class=" text-info">确定要结束吗？</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-delete" onclick="act_end();" data-dismiss="modal">确定</button>
                <input type="hidden" name="delete-qc-id"/>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-alert modal-danger fade" id="message" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-warning"></i>
            </div>
            <div class="modal-title"></div>
            <div class="modal-body">
                <input type="hidden" value="" id="message_tmp_id"/>
                <span class=" text-info">确定要提醒吗？</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-delete" onclick="act_message();" data-dismiss="modal">确定</button>
                <input type="hidden" name="delete-qc-id"/>
            </div>
        </div>
    </div>
</div>
<span id="tmp_id" val="" hidden>
{include file="$tpl_dir_base/footer.tpl"}