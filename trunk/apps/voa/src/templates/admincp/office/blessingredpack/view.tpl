{include file="$tpl_dir_base/header.tpl"}

<div id="tpl">

</div>
<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">领取详情</h4>
            </div>
            <div class="modal-body text-center">
                    <form class="form-horizontal" role="form" >
                        <table class="table table-striped table-bordered table-hover font12" id="table_mul">
                            <colgroup>
                                <col class="t-col-8"/>
                                <col class="t-col-14"/>
                                <col class="t-col-14"/>
                                <col class="t-col-12"/>
                                <col class="t-col-10"/>
                                <col class="t-col-9"/>
                            </colgroup>
                            <thead>
                            <tr>
                                <th>排名</th>
                                <th>领取时间</th>
                                <th>领取人</th>
                                <th>所在部门</th>
                                <th>领取状态</th>
                                <th>领取金额(元)</th>
                            </tr>
                            </thead>

                            <tbody id="tbdoy_id">

                            </tbody>

                        </table>

                    </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="id-download" onclick="exportExcel()" class="btn btn-warning form-small form-small-btn margin-left-12"><i class="fa fa-cloud-download"></i> 导出</button>
                <button type="button" id="syncButton" onclick="syncWePayResult()" class="btn btn-info"><span class="fa fa-refresh"></span>&nbsp;同步支付状态</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 红包详情 -->
<script type="text/html" id="tpl-detail">
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title font12"><strong><%=data.actname %></strong></h3>
    </div>
    <div class="panel-body">
        <dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
            <dt>类型：</dt>
            <dd><%=data['_type']%></dd>
            <dt>红包总金额：</dt>
            <dd>￥<%=data['_total']%>&nbsp;元</dd>
            <dt>被领取金额：</dt>
            <dd>￥<%=data['_received']%>&nbsp;元</dd>
            <dt>时间：</dt>
            <dd><%=data['_starttime']%>&nbsp;至&nbsp;<%=data['_endtime']%></dd>
            <dt>红包(剩余/总数)个数：</dt>
            <dd><%=data['_remained_number']%>/<%=data['redpacks']%>&nbsp;个</dd>
            <dt>领取详情：</dt>
            <dd><button class="btn btn-info" onclick="receive_detail()">查看</button></dd>
            <dt>分享链接数：</dt>
            <dd><%=data['share_num']%>&nbsp;个</dd>
            <dt>链接查看数：</dt>
            <dd><%=data['see_num']%>&nbsp;个</dd>
        </dl>
    </div>
</div>

<table class="table table-striped table-hover table-bordered font12 table-light">
    <colgroup>
        <col class="t-col-6" />
        <col class="t-col-12" />
        <col class="t-col-18" />
        <col />
    </colgroup>
    <thead>
    <tr>
        <th>序号</th>
        <th>祝福人</th>
        <th>祝福内容</th>
    </tr>
    </thead>
    <tbody>
        <% if(null != data && data['_persons'].length > 0) { %>
            <% for(var i=0; i<data['_persons'].length; i++) { %>
                <tr>
                    <td><%=i+1%></td>
                    <td><%=data['_persons'][i]['m_username']%></td>
                    <td><%=data['_content'][i]%></td>
                </tr>
            <% } %>
        <% } %>
    </tbody>
</table>
</script>


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


<!-- 领取详情 -->
<script type="text/html" id="tpl-receive">

    <% if(null != data && data.length > 0) { %>
        <% for(var i=0; i<data.length; i++) { %>
        <tr>
            <td><%=data[i]['ranking']%></td>
            <td><%=data[i]['_redpack_time']%></td>
            <td><%=data[i]['m_username']%></td>
            <td><%=data[i]['dep_name']%></td>
            <td><%=data[i]['_redpack_status']%></td>
            <td><%=data[i]['_money']%></td>
        </tr>
        <% } %>
        <tr>
            <td colspan="8" class="text-right vcy-page"><%=#multi%></td>
        </tr>
    <% }else { %>
        <tr>
            <td colspan="8" class="warning">暂无红包领取信息</td>
        </tr>
    <% } %>

</script>

<script type="text/javascript">

    var param = {
        'id' : {$id},
        'page' : '1',
        'limit' : '10'
    };

    $(function(){
        $.ajax({
            url:'/BlessingRedpack/Apicp/BlessingRedpackCp/detail_get',
            dataType: 'json',
            data:param,
            type:'get',
            success:function(result){
                console.log(result);
                if(result.errcode == 0){
                    var data = {
                        'data' : result.result
                    };
                    var html = template('tpl-detail', data);
                    $('#tpl').html(html);
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
            }
        })
    });

    /*领取详情*/
    function receive_detail(type){
        if(!type){
            $('#myModal').modal('show');
        }

        $.ajax({
            url:'/BlessingRedpack/Apicp/BlessingRedpackCp/list_receive',
            dataType: 'json',
            data:param,
            type:'get',
            success:function(result){
                if(result.errcode == 0){
                    var data = {
                        'data' : result.result.list,
                        'multi' : result.result.multi
                    };
                    var html = template('tpl-receive', data);
                    $('#tbdoy_id').html(html);
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
            }
        })
    }

    /*分页点击事件*/
    function pageOnclick(page) {

        param.page = page;
        $.ajax({
            url:'/BlessingRedpack/Apicp/BlessingRedpackCp/list_receive',
            dataType: 'json',
            data:param,
            type:'get',
            success:function(result){
                console.log(result.result.list);
                if(result.errcode == 0){
                    var data = {
                        'data' : result.result.list,
                        'multi' : result.result.multi
                    };
                    var html = template('tpl-receive', data);
                    $('#tbdoy_id').html(html);
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
            }
        });

    }

    /**
     * 同步微信支付状态
     * @param obj
     */
    function syncWePayResult(){
        var btn = $('#syncButton').button('loading');
        var data = {
            'redpack_id': {$id}
        };
        $.ajax({
            type:"get",
            url:"/BlessingRedpack/Apicp/BlessingRedpackLog/syncWePayResult",
            data:data,
            success: function(result) {
                if(result.errcode == 0){
                    receive_detail(true);
                }
                btn.button('reset');
            },
            error: function(result){
                btn.button('reset');
            }
        });

    }

    /**
     * 导出领取详情
     */
    function exportExcel(){
        window.location.href='/BlessingRedpack/Apicp/BlessingRedpackLog/exportExcel?redpack_id=' + {$id};
    }
</script>

{include file="$tpl_dir_base/footer.tpl"}