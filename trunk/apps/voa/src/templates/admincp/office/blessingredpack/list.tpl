{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
    <div class="panel-heading"><strong>搜索{$module_plugin['cp_name']|escape}</strong></div>
    <div class="panel-body">

        <div class="form-inline vcy-from-search" role="form" >
            {*<input type="hidden" name="issearch" value="1" />*}
            <div class="form-row">
                <div class="form-group">
                    <label class="vcy-label-none" for="id_ac_subject">活动主题：</label>
                    <input type="text" class="form-control form-small" id="id_actname" name="actname" placeholder="请输入活动主题" value="{$search_by['actname']|escape}" maxlength="30" />
                </div>
                <button type="submit" class="btn btn-info form-small form-small-btn margin-left-12" onclick="search()"><i class="fa fa-search"></i> 搜索</button>
            </div>
        </div>
    </div>
</div>
<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">红包列表</div>
    </div>
    <form class="form-horizontal" role="form" method="post" action="{$form_del_url}">
        <input type="hidden" name="formhash" value="{$formhash}" />
        <table class="table table-striped table-bordered table-hover font12" id="table_mul">
            <colgroup>
                <col class="t-col-5" />
                <col class="t-col-15" />
                <col class="t-col-10"/>
                <col class="t-col-14" />
                <col class="t-col-14"/>
                <col class="t-col-9" />
                <col class="t-col-9" />
                <col class="t-col-9" />
                <col class="t-col-13" />
            </colgroup>
            <thead>
            <tr>
                <th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');" /><span class="lbl">全选</span></label></th>
                <th>活动主题</th>
                <th>类型</th>
                <th>红包总金额(单位:元)</th>
                <th>被领取金额(单位:元)</th>
                <th>红包数量(剩余/总数)</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th>操作</th>
            </tr>
            </thead>

            <tbody id="tbdoy_id">

            </tbody>
        </table>

    </form>
</div>



<!-- Success -->
<div id="modals-success" class="modal modal-alert modal-success fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="modal-title"></div>
            <div class="modal-body">操作成功</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">确定</button>
            </div>
        </div> <!-- / .modal-content -->
    </div> <!-- / .modal-dialog -->
</div> <!-- / .modal -->
<!-- / Success -->

<!-- error modal -->
<div id="modals-error" class="modal modal-alert modal-danger fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-times-circle"></i>
            </div>
            <div class="modal-title" id="model_title"></div>
            <div class="modal-body" id="model_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>
<!-- error modal end -->


<script type="text/javascript">

    var limit = 10;
    var page = 1;

    var data = {
        'page' : page,
        'limit' : limit
    };

    var pluginId;

    $(function(){

        pluginId = {$pluginId};

        $.ajax({
            url:'/BlessingRedpack/Apicp/BlessingRedpackCp/list_page',
            data:data,
            type:'get',
            success:function(result){
                console.log(result.result.list);
                if(result.errcode == 0){
                    result.result['pluginId'] = pluginId;
                    result.result['viewUrl'] = '{$view_url_base}';
                    result.result['delUrl'] = '0';
                    result.result['editUrl'] = '0';
                    $('#tbdoy_id').html(txTpl('tpl-list', result.result));
                    _page();
                }else{
                    if(typeof (result) == 'string'){
                        $("#model_title").html('4300200');
                        $("#model_body").html('系统繁忙,请稍后再试');
                    }else{
                        $("#model_title").html(result.errcode);
                        $("#model_body").html(result.errmsg);
                    }

                    $('#modals-error').modal('show');
                }
            },
            error: function(result){
                $("#model_title").html('4300200');
                $("#model_body").html('系统繁忙,请稍后再试');
                $('#modals-error').modal('show');
            }
        })
    });

    /*条件查询*/
    function search(){
        var actname = document.getElementById('id_actname').value;
        data.actname = actname;
        _post(data);
    }

    function _page() {
        $('#table_mul .pagination a').on('click', function(){
            var page = 'page';
            var result =$(this).attr('href').match(new RegExp("[\?\&]" + page+ "=([^\&]+)", "i"));
            if (result == null || result.length < 1) {
                return '';
            }
            data.page = result[1];
            _post(data);
            return false;
        });
    }
    function _post(data){
        $.ajax({
            url:'/BlessingRedpack/Apicp/BlessingRedpackCp/list_page',
            data:data,
            type:'get',
            success:function(result){
                result.result['pluginId'] = pluginId;
                result.result['viewUrl'] = '{$view_url_base}';
                result.result['delUrl'] = '0';
                result.result['editUrl'] = '0';
                $('#tbdoy_id').html(txTpl('tpl-list', result.result));
                _page();
            },
            error: function(result){
                $("#model_title").html('4300200');
                $("#model_body").html('系统繁忙,请稍后再试');
                $('#modals-error').modal('show');
            }
        });
    }

    /*下载自由红包二维码*/
    function downloadQcode(id){
        //console.log(id);

        bootbox.alert('<img style="width:400;" src="/BlessingRedpack/Apicp/BlessingRedpackCp/qrcodeDownload_get?id='+id+'&isDownload=0"/>'+'<div><a href="/BlessingRedpack/Apicp/BlessingRedpackCp/qrcodeDownload_get?id='+id+'&isDownload=1"><button class="btn">保存到本地</button></a></div>');
        $('.modal-dialog').width(450);
        $('.modal-footer').hide();
    }




</script>
{literal}
<script type="text/template" id="tpl-list">


    <% if (!jQuery.isEmptyObject(list)) { %>
    <% $.each(list,function(n,val){ %>

    <tr>
        <td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[<%=val['id']%>]" value="<%=val['id']%>" /><span class="lbl"> </span></label></td>
        <td><%=val['actname']%></td>
        <td><%=val['_type']%></td>
        <td><%=val['_total']%></td>
        <td><%=val['_received']%></td>
        <td><%=val['_remained_number']%>/<%=val['redpacks']%></td>
        <td><%=val['_starttime']%></td>
        <td><%=val['_endtime']%></td>
        <td>
            <% if(viewUrl != '0') { %>
                <a href="<%=viewUrl%><%=val['id']%>"><i class="fa fa-eye"></i> 详情</a>
            <% } %>
            <% if(editUrl != '0') { %>
                <a href="<%=editUrl%><%=val['id']%>"><i class="fa fa-edit"></i> 编辑</a>
            <% } %>
            <% if(delUrl != '0') { %>
                <a href="javascript:void(0);" onclick="deleteRedpack('<%=val['id']%>')" class="text-danger _delete"><i class="fa fa-times"></i> 删除</a>
            <% } %>
            <% if(val['type'] == '4') { %>
                <a href="javascript:void(0);" onclick="downloadQcode('<%=val['id']%>')"><i class="fa fa-download"></i>查看二维码</a>
            <% } %>
        </td>
    </tr>

    <% }) %>
    <% }else{ %>

    <tr>
        <td colspan="9" class="warning">

            <% if (issearch) { %>
                未搜索到指定条件的红包信息
            <% }else{ %>
                暂无红包信息
            <% } %>

        </td>
    </tr>
    <% } %>

    <tr>
        <td colspan="9" class="text-right vcy-page"><%=multi%></td>

    </tr>




</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}