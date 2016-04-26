{include file="$tpl_dir_base/header.tpl"}

<style type="text/css">
    #tip {
        float:left;
        width:330px;
        font-size: 14px;
    }
    #tip li {
        margin: 12px 0;
    }
    .search-contacts {
        margin-top: 10px;
        margin-left: 10px;
    }
    .mod_photo_uploader {
        height: 38px;
    }
</style>
<div id="tpl">

</div>
<script type="text/html" id="tpl-setting">
<div class="panel panel-default">
    <div class="panel-body">
        {*<form class="form-horizontal font12" role="form" >*}
            {*<input type="hidden" name="formhash" value="{$formhash}" />*}

            <div class="form-group">
                <label for="logo" class="col-sm-2 control-label">微信cert证书</label>
                <div class="col-sm-7" id="logo">
                    <div class="uploader_box">
                        <input type="hidden" class="_input" name="logo[at_id]" value="">
                        <span class="btn btn-success fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>上传</span>
                            <input class="cycp_uploader" type="file" name="file" data-url="/admincp/api/attachment/upload/?file=file" data-callback="uploadCallback(result, 'file_id_1', '1')" data-callbackall="" data-hidedelete="0" data-showimage="0">

                        </span>

                        <span class="_showdelete" style="display: none" id="span_id_1"><a href="javascript:;" class="btn btn-danger"  onclick="deleteFile('file_id_1', 'span_id_1', 'wxpay_certificate1')">删除</a></span>

                        <% if(data['setting']['wxpay_certificate1'][1] == 'undefined') { %>
                            <span class="_showimage" id="file_id_1"></span>
                        <% }else { %>
                            <span class="_showimage" id="file_id_1"><%=data['setting']['wxpay_certificate1'][1] %></span>
                        <% } %>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="logo" class="col-sm-2 control-label">微信key证书</label>
                <div class="col-sm-7" id="logo">
                    <div class="uploader_box">
                        <input type="hidden" class="_input" name="logo[at_id]" value="">
                        <span class="btn btn-success fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>上传</span>
                            <input class="cycp_uploader" type="file" name="file" data-url="/admincp/api/attachment/upload/?file=file" data-callback="uploadCallback(result, 'file_id_2', '2')" data-callbackall="" data-hidedelete="0" data-showimage="0">

                        </span>
                        <span class="_showdelete" id="span_id_2" style="display: none"><a href="javascript:;" class="btn btn-danger " onclick="deleteFile('file_id_2', 'span_id_2', 'wxpay_certificate2')">删除</a></span>
                        <% if(data['setting']['wxpay_certificate2'][1] == 'undefined') { %>
                            <span class="_showimage" id="file_id_2"></span>
                        <% }else { %>
                            <span class="_showimage" id="file_id_2"><%=data['setting']['wxpay_certificate2'][1] %></span>
                        <% } %>

                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="logo" class="col-sm-2 control-label">微信ca证书</label>
                <div class="col-sm-5" id="logo">
                    <div class="uploader_box">
                        <input type="hidden" class="_input" name="logo[at_id]" value="">
                        <span class="btn btn-success fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>上传</span>
                            <input class="cycp_uploader" type="file" name="file" data-url="/admincp/api/attachment/upload/?file=file" data-callback="uploadCallback(result, 'file_id_3', '3')" data-callbackall="" data-hidedelete="0" data-showimage="0">

                        </span>
                        <span class="_showdelete" id="span_id_3" style="display: none"><a href="javascript:;" class="btn btn-danger" onclick="deleteFile('file_id_3', 'span_id_3', 'wxpay_certificate3')">删除</a></span>

                        <% if(data['setting']['wxpay_certificate3'][1] == 'undefined') { %>
                            <span class="_showimage" id="file_id_3"></span>
                        <% }else { %>
                            <span class="_showimage" id="file_id_3"><%=data['setting']['wxpay_certificate3'][1] %></span>
                        <% } %>
                    </div>
                </div>
            </div>
            <div style="display: none;">{cycp_upload}</div>
            <div class="form-group">
                <label for="mchid" class="col-sm-2 control-label">微信支付商家号</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="mchid" name="mchid" value="<%=data['setting']['mchid'] %>" maxlength="32" />

                </div>
            </div>
            <div class="form-group">
                <label for="mchkey" class="col-sm-2 control-label">微信支付商家秘钥</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="mchkey" name="mchkey"  value="<%=data['setting']['mchkey'] %>" maxlength="32" />
                </div>
            </div>

            <div class="form-group">
                <label for="redpack_min" class="col-sm-2 control-label text-danger">最小红包(单位:元)</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="redpack_min" name="redpack_min" placeholder="1" value="<%=data['setting']['redpack_min'] %>" maxlength="30" required="required" />
                    拼手气红包和自由红包，随机生成红包的最小值为1元，最大值为20000元
                </div>
            </div>
            <div class="form-group">
                <label for="redpack_max" class="col-sm-2 control-label text-danger">最大红包(单位:元)</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="redpack_max" name="redpack_max" placeholder="200" value="<%=data['setting']['redpack_max'] %>" maxlength="30" required="required" />
                    拼手气红包和自由红包，随机生成红包的最小值为1元，最大值为20000元
                </div>
            </div>

            <div class="form-group">
                <label for="department" class="col-sm-2 control-label">邀请部门</label>
                <div class="col-sm-10">

                    <div class="angularjs-area"  data-ng-controller="ChooseShimCtrl">
                        {*<div id="deps_container" class="col-sm-8">*}
                            <a class="btn btn-default js-call-contacts" id="department" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')" style="width:120px;"><i class="fa fa-plus"></i>&nbsp;选择部门</a>
                        {*</div>*}
                    </div>

                </div>
                <label for="redpack_max" class="col-sm-2 control-label text-danger"></label>
                <div class="col-sm-10">
                    【自由红包】，人员被邀请加入后指定部门
                </div>
            </div>



            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button onclick="saveSubmit()" class="btn btn-primary">保存</button>
                    &nbsp;&nbsp;
                    <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
                </div>
            </div>
        {*</form>*}
    </div>
</div>
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


<script  type="text/javascript">

    var dep_arr = [];

    var data = {
        'wxpay_certificate1' : '',
        'wxpay_certificate2' : '',
        'wxpay_certificate3' : ''
    };
    var certificates_arr = new Array();

    /*上传回调*/
    function uploadCallback(result, id, index){
        $('#'+id).text(result.file[0].name);
        var tmp = result.id+","+result.file[0].name;
        data['wxpay_certificate'+index] = tmp;
    }

    function deleteFile(file_id, span_id, id){
        var result = confirm('是否删除?');
        if(result){
            $('#'+file_id).text('');
            data[id] = '';
            $('#'+span_id).hide();
        }
    }

    /*保存*/
    function saveSubmit(){
//        for(var k in data){
//            if(!data[k] || data[k] == "undefined,undefined"){
//                alert('请上传微信支付证书');
//                return false;
//            }
//        }

        var min = parseInt($('#redpack_min').val());
        var max = parseInt($('#redpack_max').val());
        if(min < 1){
            $.growl.error({ title: "错误", message: "最小值不能小于1元"});
            return false;
        }
        if(min >= max){
            $.growl.error({ title: "错误", message: "最小红包必须小于最大红包"});
            return false;
        }

//        if(dep_arr.length == 0 ){
//            alert('请选择邀请部门');
//            return;
//        }
        if(dep_arr.length > 0){
            data.invite_department = dep_arr[0]['id'];
        }

        var btn = $('#saveSubmit').button('loading');
        data.redpack_min = $('#redpack_min').val();
        data.redpack_max = $('#redpack_max').val();
        data.mchid = $('#mchid').val();
        data.mchkey = $('#mchkey').val();


        $.ajax({
            type:"post",
            url:"/BlessingRedpack/Apicp/BlessingRedpackSettingCp/update_setting",
            data:data,
            success: function(result) {
                if(result.errcode != 0){
                    if(typeof (result) == 'string'){
                        $("#model_title").html('4300200');
                        $("#model_body").html('系统繁忙,请稍后再试');
                    }else{
                        $("#model_title").html(result.errcode);
                        $("#model_body").html(result.errmsg);
                    }
                    $('#modals-error').modal('show');
                }else{
                    $('#modals-success').modal('show');
                }
                btn.button('reset');
            },
            error: function(result){
                btn.button('reset');
                $("#model_title").html('4300200');
                $("#model_body").html('系统繁忙,请稍后再试');
                $('#modals-error').modal('show');
            }
        });

    }
    $(function(){

        $.ajax({
            url:'/BlessingRedpack/Apicp/BlessingRedpackSettingCp/setting',
            type:'get',
            success:function(result){
                //console.log(result);
                if(result.errcode == 0){
                    var wxpay_certificate1 = result.result.setting['wxpay_certificate1'];
                    var wxpay_certificate2 = result.result.setting['wxpay_certificate2'];
                    var wxpay_certificate3 = result.result.setting['wxpay_certificate3'];
                    if(wxpay_certificate1 != ''){
                        data.wxpay_certificate1 = wxpay_certificate1[0]+','+wxpay_certificate1[1];
                    }
                    if(wxpay_certificate2 != ''){
                        data.wxpay_certificate2 = wxpay_certificate2[0]+','+wxpay_certificate2[1];
                    }
                    if(wxpay_certificate3 != ''){
                        data.wxpay_certificate3 = wxpay_certificate3[0]+','+wxpay_certificate3[1];
                    }

                    var dt = {
                        'data' : result.result
                    };

                    //console.log(result.result);
                    var invite_department = result.result.setting['invite_department'];
                    var dempartmentName = result.result.setting['dempartmentName'];

                    if(invite_department){
                        var tmpObj = {};
                        tmpObj.id =  invite_department;
                        tmpObj.isChecked =  true;
                        dep_arr[0] = tmpObj;
                    }

                    var html = template('tpl-setting', dt);
                    $('#tpl').html(html);
                    angular.bootstrap($('#tpl'), ['ng.poler.plugins.pc']);

                    $('#department').text(dempartmentName);
                }
            },
            error: function(result){
                $("#model_title").html('4300200');
                $("#model_body").html('系统繁忙,请稍后再试');
                $('#modals-error').modal('show');
            }
        });


    });

    //模态框当调用 hide 实例方法时触发
    $('#modals-success').on('hide.bs.modal', function() {
        window.location.href = "/admincp/office/blessingredpack/setting/pluginid/{$pluginId}/";
    });

    /*选择部门回调*/
    function selectedDepartmentCallBack(data){
        //console.log('选择的部门: ', data);
        if(data.length == 0){
            dep_arr = [];
            $('#department').html('<i class="fa fa-plus"></i>&nbsp;选择部门');
            return;
        }

        if(data[0].id == 0){
           data.shift();
        }

        dep_arr = data;
        var tmp;
        if(data.length > 0){
            tmp = data[0].name;
            tmp = tmp.substring(0,7)+'...';
        }

        $('#department').text(tmp);
    }
</script>
{include file="$tpl_dir_base/footer.tpl"}