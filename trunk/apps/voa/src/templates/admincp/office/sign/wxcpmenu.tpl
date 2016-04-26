{include file="$tpl_dir_base/header.tpl"}
<div id="tpl">

</div>
<script type="text/html" id="tpl-setting">
<div class="alert">
	<button type="button" class="close" data-dismiss="alert">×</button><strong>提示：</strong>发布最多24小时内会更新到新的菜单。
</div>
<link href="{$CSSDIR}category.css" rel="stylesheet" type="text/css" />
<form class="form-horizontal font12" id="form">
	<div class="panel panel-warning">
		<div class="panel-heading">
			<strong>微信菜单</strong>
		</div>
		<div class="panel-body">

            <% if (null != data && data.length > 0) { %>
                <% for(var i=0; i<data.length; i++) { %>
                    <div class="form-group font12">
                        <label for="late_range" class="col-sm-3 control-label text-right"><span style="color:#ff0000">* &nbsp;</span>菜单<%=i+1%></label>

                        <div class="col-sm-9" id="div_<%=i+1%>">
                            <input type="text" class="form-control" id="name_<%=i+1%>" name="name_<%=i+1%>" value="<%=data[i]['name']%>" maxlength="5" required="required">
                            <input type="hidden" id="url_<%=i+1%>" value ="<%=data[i]['url']%>">
                            <input type="hidden" id="type_<%=i+1%>" value="<%=data[i]['type']%>">
                            <p class="help-block">最多输入5个字符</p>
                        </div>
                    </div>
                <% } %>
            <% } %>

			<div class="form-group font12">
				<label for="work_begin_hi" class="col-sm-3 control-label text-right"></label>

				<div class="col-sm-9">
					<input type="button" id="submit" onclick="saveSubmit()" class="btn btn-success" value="发布">
                    &nbsp;&nbsp;
                    <a href="javascript:history.go(-1);" role="button" class="btn btn-default" >取消</a>
				</div>
			</div>

		</div>

</form>
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

<script>
    $(function(){
        $.ajax({
            url: '/Sign/Apicp/SignSettingCp/setting',
            type: 'get',
            success: function (result) {
                if (result.errcode == 0) {
                    var setting = result.result.setting;
                    $('#outSign').val(setting['wxcpmenu']);
                    var data = {
                        'data' : setting['wxcpmenu']
                    };

                    var html = template('tpl-setting', data);
                    $('#tpl').html(html);

                } else {
                    if (typeof (result) == 'string') {
                        $("#model_title").html('4300200');
                        $("#model_body").html('系统繁忙,请稍后再试');
                    } else {
                        $("#model_title").html(result.errcode);
                        $("#model_body").html(result.errmsg);
                    }

                    $('#modals-error').modal('show');
                }
            },
            error: function (result) {
                $("#model_title").html('4300200');
                $("#model_body").html('系统繁忙,请稍后再试');
                $('#modals-error').modal('show');
            }
        });
    });

    var data = { };
    var menu_array = [];
    function saveSubmit(){
        var btn = $('#submit').button('loading');
        menu_array = [];
        for(var i=1; i<=3; i++){
            var name = $("#name_"+i).val();
            if(name.replace(/(^\s*)|(\s*$)/g,"")==""){
                $.growl.error({ title: "错误", message: "请输入菜单"+i+"的名称"});
                btn.button('reset');
                return;
            }
            var tmp = {
                'name' : name,
                'url' : $("#url_"+i).val(),
                'type' : $("#type_"+i).val()
            };
            menu_array.push(tmp);
        }
        data['menu_array'] = menu_array;

        $.ajax({
            url: '/Sign/Apicp/SignSettingCp/update_wxcpmenu',
            data: data,
            type: 'post',
            success: function (result) {
                if (result.errcode == 0) {
                    $('#modals-success').modal('show');
                } else {
                    if (typeof (result) == 'string') {
                        $("#model_title").html('4300200');
                        $("#model_body").html('系统繁忙,请稍后再试');
                    } else {
                        $("#model_title").html(result.errcode);
                        $("#model_body").html(result.errmsg);
                    }

                    $('#modals-error').modal('show');
                }

                btn.button('reset');
            },
            error: function (result) {
                btn.button('reset');
                $("#model_title").html('4300200');
                $("#model_body").html('系统繁忙,请稍后再试');
                $('#modals-error').modal('show');
            }
        });
    }

    //模态框当调用 hide 实例方法时触发
    $('#modals-success').on('hide.bs.modal', function() {
        window.location.href = "/admincp/office/sign/wxcpmenu/pluginid/{$pluginId}";
    });
</script>




{include file="$tpl_dir_base/footer.tpl"}