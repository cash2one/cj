{include file="$tpl_dir_base/header.tpl"}

<style type="text/css">
    .datepicker-orient-top{
        z-index: 999999!important;
    }

    #formId div.has-error
    {
        color:Red;
        font-size:13px;
    }
    .form-control-widt{
        position: relative;
    }
    .form-control-width input{
        width:100%;
        float:left;
    }
    .form-control-width span{
        float:left;
        margin:8px 0 0 3px;
        position: absolute;
        position: absolute;
        right: -10px;
        top: 0;
    }
    .form-control-width div{
        width: 100%;
        float: left;
    }
    .img-width{
        float: left;
        width: 140px;
        text-align: center;
        line-height: 32px;
    }

</style>

<div class="panel panel-default font12">
<div class="panel-body">
<form class="form-horizontal font12" id="formId" role="form"  >
<input type="hidden" name="formhash" value="{$formhash}" />

<div class="form-group">

    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>活动主题</label>

    <div class="col-sm-9">
        <input type="text" class="form-control form-small" id="actname" name="actname" placeholder="最多为20个字"  maxlength="20"  required="required"/>
    </div>
</div>
<div class="form-group">
    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>被邀请语</label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-small" id="inviteContent" name="inviteContent" placeholder="最多为10个字"  maxlength="10"  required="required"/>
    </div>
</div>
<div class="form-group">
    <label class="control-label col-sm-2">类型</label>
    <div class="col-sm-2">
        <input type="radio" id="randowType" value="1" onclick="changType(this)" checked="checked"/>&nbsp;&nbsp;
        <label for="randowType">拼手气红包</label>
    </div>
    <div class="col-sm-2">
        <input type="radio" id="avgType" value="2" onclick="changType(this)"/>&nbsp;&nbsp;
        <label for="avgType">普通红包</label>
    </div>
    <div class="col-sm-2">
        <input type="radio" id="freeType" value="4" onclick="changType(this)"/>&nbsp;&nbsp;
        <label for="freeType">自由红包</label>
    </div>
    <input type="hidden" id="type" name="type" value="1"/>
</div>
<div class="form-group" style="display: none;" id="single_disp">
    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000;">* &nbsp;</span>单个金额</label>
    <div class="col-sm-2 form-control-width">
        <input type="text" type="hidden" class="form-control form-small form-control-width" id="single" value="1" name="single" placeholder="填写金额"  maxlength="10"  required="required"/>&nbsp;<span>元</span>
    </div>
</div>
<div class="form-group" id="random_disp">
    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>总金额</label>
    <div class="col-sm-2 form-control-width">
        <input type="text" class="form-control form-small" id="total" name="total" value="1" placeholder="填写金额"  maxlength="10"  required="required"/>&nbsp;<span>元</span>
    </div>
</div>
<div id="free_disp" style="display: none;" >
<div class="form-group" >
    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>红包总数</label>
    <div class="col-sm-2 form-control-width">
        <input type="text" class="form-control form-small" id="free_sum" name="redpack_sum" value="1" placeholder="填写红包总数"  maxlength="10"  required="required"/>&nbsp;<span>个</span>
    </div>
</div>
<div class="form-group" >
    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>红包总金额</label>
    <div class="col-sm-2 form-control-width">
        <input type="text" class="form-control form-small" id="free_money" name="redpack_money" value="1" placeholder="填写红包总金额"  maxlength="10"  required="required"/>&nbsp;<span>元</span>
    </div>
</div>
</div>

<div class="form-group">
    <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>红包内容</label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-small" id="wishing" name="wishing" placeholder="最多为15个字"  maxlength="15"  required="required"/>
    </div>
</div>

<div id="specified_disp">
    <div class="form-group">
        <label class="control-label col-sm-2" for="id_rights">领取对象</label>
        <div class="col-sm-9">
            <div>
                <button type="button" class="btn btn-primary" id="all_btn">全公司</button>
                <input type="hidden" id="allCompany" name="allCompany" value="0">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn" id="specified_btn">指定对象</button>
            </div>

            <div id="invite_user_dep_container" style="display: none">
                <hr>
                <div class="angularjs-area" data-ng-app="ng.poler.plugins.pc" data-ng-controller="ChooseShimCtrl">
                    <div class="row">
                        <label class="col-sm-2 text-right padding-sm">选择部门：</label>
                        <div id="deps_container" class="col-sm-8">
                            <a class="btn btn-default js-call-contacts" id="department" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')" style="width:120px;"><i class="fa fa-plus"></i>&nbsp;选择部门</a>

                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <label class="col-sm-2 text-right padding-sm">选择人员：</label>
                        <div id="users_container" class="col-sm-8">
                            <a class="btn btn-default js-call-contacts" id="person" data-ng-click="selectPerson('user_arr','selectedPersonCallBack')" style="width:120px;"><i class="fa fa-plus"></i>&nbsp;选择人员</a>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="specifiedRequired" name="specifiedRequired" required="required"/>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <label class="control-label col-sm-2" for="id_rights">设置祝福</label>
    <div class="col-xs-9">
        <a  class="btn btn-default" onclick="addBlessUser()"><i class="fa fa-plus"></i>&nbsp;增加祝福人</a>
    </div>
</div>

<!-- 祝福人模板 begin -->
<script id="bless_template_id" type="text/html">
    <div id="del_div_<%=blessIndex%>">
        <div class="form-group">
            <label class="control-label col-sm-2" ></label>
            <div class="col-sm-9">
                <div class="well center-block" >
                    <div class="form-group">
                        <label class="control-label col-sm-2" ><span style="color:#ff0000">* &nbsp;</span>第<%=blessIndex+1%>祝福人</label>
                        <button type="button" class="btn btn-danger" onclick="delBlessUser('<%=blessIndex%>')">删除</button>
                        <div class="col-xs-9">
                            <div class="angularjs-area"  data-ng-controller="ChooseShimCtrl">
                                <a class="btn btn-default" id="bless_person_<%=blessIndex%>" data-ng-click="selectPerson('tmp_bless_person_arr[<%=blessIndex%>]','selectedBlessPersonCallBack','<%=blessIndex%>')"><i class="fa fa-plus"></i>&nbsp;祝福人</a>
                                <input type="hidden" id="bless_<%=blessIndex%>" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="title"><span style="color:#ff0000">* &nbsp;</span>祝福语</label>
                        <div class="col-sm-9">
                            <textarea id="language_<%=blessIndex%>" name="validate_textarea" class="m-wrap span12" rows="3" style="height: 100px;width: 500px;max-width: 589px;"  maxlength="600" ></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="title"></label>
                        <div class="col-sm-9">
                            <button style="margin:5px 5px 0 0;" type="button" class="btn btn-info" onclick="insertText(document.getElementById('language_<%=blessIndex%>'),'[departmentTag]')">插入被祝福人部门[departmentTag]</button>
                            <button style="margin:5px 5px 0 0;" type="button" class="btn btn-info" onclick="insertText(document.getElementById('language_<%=blessIndex%>'),'[jobTag]')">插入被祝福人职位[jobTag]</button>
                            <button style="margin:5px 5px 0 0;" type="button" class="btn btn-info" onclick="insertText(document.getElementById('language_<%=blessIndex%>'),'[userNameTag]')">插入被祝福人姓名[userNameTag]</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
<!-- 祝福人模板 end -->

<div id="bless_div_id">

</div>
<input type="hidden" id="blessHiddenObj" name="blessHiddenObj"/>

<div class="form-group">
    <label class="control-label col-sm-2">开始时间</label>
    <script>
        init.push(function () {
            var options1 = {
                todayBtn: "linked",
                orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
                startDate: new Date()

            };
            $('#start_data').datepicker(options1);
            $('#start_time').timepicker({
                minuteStep: 1,
                secondStep: 1,
                showSeconds: true,
                showMeridian: false,
                showInputs: true
            });
        });
    </script>
    <div class="col-sm-9">
        <div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
            <div style="width: 300px">
                <input value="" type="text" class="input-sm form-control" id="start_data" name="startTime[data]" placeholder="开始日期" style="width: 150px"/>
                <input value="00:00:00" required="required" type="text" class="input-sm form-control" id="start_time" name="startTime[time]" style="width: 150px"/>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-sm-2">截止时间</label>
    <script>
        init.push(function () {
            var options2 = {
                todayBtn: "linked",
                orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
                startDate: new Date()

            };
            $('#end_data').datepicker(options2);
            $('#end_time').timepicker({
                minuteStep: 1,
                secondStep: 1,
                showSeconds: true,
                showMeridian: false,
                showInputs: true
            });
        });
    </script>
    <div class="col-sm-9">
        <div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
            <div style="width: 300px">
                <input value=""  type="text" class="input-sm form-control" id="end_data" name="endTime[data]" placeholder="截止日期" style="width: 150px"/>
                <input value="23:59:59"  type="text" class="input-sm form-control" id="end_time" name="endTime[time]" style="width: 150px"/>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-sm-2" for="id_rights">背景设置</label>
    <div class="col-xs-9">
        <div class="img-width">
            <img  class="img-thumbnail" src="/admincp/static/images/chb-bg.jpg" alt="140x140" id="imgReceiveBackgrund" onclick="fileSelect('imgReceiveBackgrund2')" data-holder-rendered="true" style="width: 140px; height: 140px;">
            领取背景
            <input type="hidden" id="imgReceiveBackgrund1" name="imgReceiveBg"/>
        </div>
        <div class="img-width">
            <img  class="img-thumbnail" src="/admincp/static/images/receive-bg.jpg" alt="140x140" id="imgChatBackgrund"  onclick="fileSelect('imgChatBackgrund2')" data-holder-rendered="true" style="width: 140px; height: 140px;">
            祝福语背景
            <input type="hidden" id="imgChatBackgrund1" name="imgChatBg"/>
        </div>
        <div class="uploader_box" style="display:none;">
            <input type="hidden" class="_input" name="" value="">
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>上传图片</span>
                <input class="cycp_uploader" type="file" id="imgReceiveBackgrund2"  name="file" data-url="/admincp/api/attachment/upload/?file=file&thumbsize=45" data-callback="uploadCallback('imgReceiveBackgrund',result)" data-callbackall="" data-hidedelete="0" data-showimage="1">
            </span>
        </div>
        <div class="uploader_box" style="display:none;">
            <input type="hidden" class="_input" name="" value="">
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>上传图片</span>
                <input class="cycp_uploader" type="file" id="imgChatBackgrund2"  name="file" data-url="/admincp/api/attachment/upload/?file=file&thumbsize=45" data-callback="uploadCallback('imgChatBackgrund',result)" data-callbackall="" data-hidedelete="0" data-showimage="1">
            </span>
        </div>
        <div style="display:none;">{cycp_upload}</div>
    </div>
</div>
<div class="form-group">
    <label class="control-label col-sm-2" ></label>
    <div class="col-xs-9" >
        <div  style="color:#ff0000;">图片推荐尺寸：640 * 1008</div>
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-6">
        <a id="saveSubmit" class="btn btn-primary">保存</a>
        &nbsp;&nbsp;
        <a href="javascript:history.go(-1);" id="cancelId" role="button" class="btn btn-default" >取消</a>
    </div>
</div>
</form>
</div>
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


    var blessIndex = 0;
    var redpack_type = 1;
    var dep_arr = [];
    var user_arr = [];
    var tmp_bless_person_arr = [];
   // tmp_bless_person_arr[0] = [];
    {*所有祝福人 数据格式 {bless_person_下标:[选择的祝福人对象]}*}
    var bless_person_arr = [];

    var bless_obj = {};

    /*选择部门回调*/
    function selectedDepartmentCallBack(data){
        //console.log('选择的部门: ', data);
        if(data.length == 0){
            dep_arr = [];
            if(user_arr.length == 0){
                $('#specifiedRequired').val('');
            }
            $('#department').html('<i class="fa fa-plus"></i>&nbsp;选择部门');
            $("#formId").validate().element($("#specifiedRequired"));
            return;
        }

        if(data[0].id == 0){
            data.shift();
        }

        dep_arr = data;
        var tmp;
        if(data.length > 1){
            tmp = data[0].name+'、'+data[1].name;
            tmp = tmp.substring(0,7)+'...';
        }else{
            tmp = data[0].name;
        }
        $('#specifiedRequired').val('required');
        $('#department').text(tmp);
        $("#formId").validate().element($("#specifiedRequired"));
    }

    /*选择人员回调*/
    function selectedPersonCallBack(data){
        //console.log('选择的人员: ', data);
        if(data.length == 0){
            user_arr = [];
            if(dep_arr.length == 0){
                $('#specifiedRequired').val('');
            }
            $('#person').html('<i class="fa fa-plus"></i>&nbsp;选择人员');
            $("#formId").validate().element($("#specifiedRequired"));
            return;
        }

        user_arr = data;
        var tmp;
        if(data.length > 1){
            tmp = data[0].m_username+'、'+data[1].m_username;
            tmp = tmp.substring(0,7)+'...';
        }else{
            tmp = data[0].m_username;
        }

        $('#person').text(tmp);
        $('#specifiedRequired').val('required');
        $("#formId").validate().element($("#specifiedRequired"));
    }

    /*选择祝福人回调*/
    function selectedBlessPersonCallBack(data, id){
       // console.log('选择的祝福人: ', data);

        //由于选人组件是多选，故取第一个
        if(data.length > 0){
            //验证选择祝福人是否重复
//            if(blessIndex > 1){
//                for(var i=1; i<=blessIndex; i++){
//                    var tmpId = 'bless_person_'+i;
//                    //console.log(tmpId+'----'+id);
//                    if(bless_obj[tmpId].length > 0 ){
//                        if(tmpId != id && bless_obj[tmpId][0].m_uid == data[0].m_uid){
//                            return false;
//                        }
//                    }
//                }
//            }
            var div_id = 'bless_person_' + id;
            $('#' +  div_id).text(data[0].m_username);

            bless_obj[div_id][0]['m_uid'] = data[0]['m_uid'];
            bless_obj[div_id][0]['m_username'] = data[0]['m_username'];
            bless_obj[div_id][0]['content'] = data[0]['content'];
            var tmp = new Array();
            tmp.push(data[0]);
            tmp_bless_person_arr[id] = tmp;

            //console.log('id= ' + id, JSON.stringify(tmp));

        }

        //console.log(bless_obj);
    }

    // 选择全公司
    $('#all_btn').on('click',function(){
        $('#invite_user_dep_container .photo-scrollable').html('');
        $('#allCompany').val(0);
        $('#invite_user_dep_container').hide();
        $(this).addClass('btn-primary');
        $('#specified_btn').removeClass('btn-primary');
        $('#specifiedRequired').val('required');
    });

    // 邀请人员
    $('#specified_btn').on('click',function(){
        if(dep_arr.length == 0 && user_arr.length == 0){
            $('#specifiedRequired').val('');
        }
        $('#invite_user_dep_container').show();
        $('#allCompany').val(1);
        $(this).addClass('btn-primary');
        $('#all_btn').removeClass('btn-primary');
        $("#formId").validate().element($("#specifiedRequired"));
    });

    /*添加祝福人*/

    function addBlessUser(){

        var tmp = 'bless_person_'+blessIndex;

        // 要插入的按钮
        tmp_bless_person_arr[blessIndex] = [];

        bless_obj[tmp] = [{
            'm_uid' : '',
            'm_username' : '',
            'content' : ''
        }];

        var data = {
            blessIndex: blessIndex
        };

        var html = template('bless_template_id', data);
        var angularInit = $(html);
        $('#bless_div_id').append(angularInit);
        //由于是动态添加的DOM，angularjs 绑定的ng-click不起作用，手动angular化
//        angular.element(angularInit).ready(function() {
            angular.bootstrap(angularInit, ['ng.poler.plugins.pc']);
//        });

        blessIndex++;

    }

    function test(id, blessIndex){
        for(var i=id; i<blessIndex; i++){
            var tmpId = 'bless_person_'+i;
            if($('#' + tmpId).val() != undefined){
                var content = $('#language_'+i).val();
                bless_obj[tmpId][0]['content'] = content;
            }
        }
    }

    /*删除祝福人 */
    function delBlessUser(id){

        //删除DOM
        $('#del_div_'+id).remove();

        //删除全局保存的已选择祝福人
        var tmpId = 'bless_person_'+id;
        delete bless_obj[tmpId];


        var index = Number(id);
        test(index, blessIndex);

        //重新排序bless_obj对象下标
        for(var k=Number(id)+1; k<=blessIndex; k++){

            tmpId = 'bless_person_'+k;
            var tmpObj = bless_obj[tmpId];
            tmpId = 'bless_person_'+String(Number(k)-1);
            bless_obj[tmpId] = tmpObj;

            tmpId = 'bless_person_'+k;
            delete bless_obj[tmpId];
        }

        //重新计算下标
        blessIndex--;



        var data = {
            blessIndex: 0
        };
        //只重新渲染当前删除的下标之后的
        for(var i=index; i<=blessIndex; i++){
            $('#del_div_'+(i+1)).remove();
            tmpId = 'bless_person_'+i;
            data.blessIndex = i;
            var html = template('bless_template_id', data);
            var angularInit = $(html);
            $('#bless_div_id').append(angularInit);
            if(bless_obj[tmpId][0].m_username != ''){
                $('#bless_person_'+i).text(bless_obj[tmpId][0].m_username);
            }
            $('#language_'+i).text(bless_obj[tmpId][0].content);
            //手动angualrjs化，使绑定的选择事件生效
            angular.bootstrap(angularInit, ['ng.poler.plugins.pc']);
        }


    }

    /*插入替换标签,光标处插入替换标签*/
    function insertText(obj,str) {
        if (document.selection) {
            var sel = document.selection.createRange();
            sel.text = str;
        } else if (typeof obj.selectionStart === 'number' && typeof obj.selectionEnd === 'number') {
            var startPos = obj.selectionStart,
                    endPos = obj.selectionEnd,
                    cursorPos = startPos,
                    tmpStr = obj.value;
            obj.value = tmpStr.substring(0, startPos) + str + tmpStr.substring(endPos, tmpStr.length);
            cursorPos += str.length;
            obj.selectionStart = obj.selectionEnd = cursorPos;
        } else {
            obj.value += str;
        }
        obj.focus();//再次获取焦点
    }

    /*上传图片*/
    var imgId;
    function fileSelect(id) {
        imgId = id;
        document.getElementById(id).click();
    }
    //上传回调
    function uploadCallback(id,result){
        $('#'+id).attr('src', result.file[0].url);
        $('#'+id+'1').val(result.id);
    }

    //红包类型change事件
    function changType(obj){
        //随机红包
        if(obj.value == 1){
            $('#avgType').attr('checked', false);
            $('#freeType').attr('checked', false);
            $('#single_disp').hide();
            $('#free_disp').hide();

            $('#randowType').attr('checked', true);
            $('#single').val('1');
            $('#free_sum').val('1');
            $('#free_money').val('1');
            $('#random_disp').show();
            $('#specified_disp').show();
            if($('#allCompany').val() == 1){
                if(dep_arr.length == 0 && user_arr.length == 0){
                    $('#specifiedRequired').val('');
                }
            }



        }else if(obj.value == 2){
            $('#randowType').attr('checked', false);
            $('#freeType').attr('checked', false);
            $('#random_disp').hide();
            $('#free_disp').hide();

            $('#avgType').attr('checked', true);
            $('#single_disp').show();
            $('#specified_disp').show();
            //$('#specifiedRequired').val('');
            $('#total').val('1');
            $('#single').val('1');
            $('#free_sum').val('1');
            $('#free_money').val('1');
            if($('#allCompany').val() == 1){
                if(dep_arr.length == 0 && user_arr.length == 0){
                    $('#specifiedRequired').val('');
                }
            }

        }else if(obj.value == 4){
            //自由红包
            $('#randowType').attr('checked', false);
            $('#avgType').attr('checked', false);
            $('#single_disp').hide();
            $('#random_disp').hide();
            $('#specifiedRequired').val('required');
            $('#total').val('1');
            $('#single').val('1');
            $('#freeType').attr('checked', true);
            $('#free_disp').show();
            $('#specified_disp').hide();

            redpack_type = 4;

            /*动态添加jquery validate 校验规则*/
            $("#free_sum").rules("add",{
                required: true,
                validateMoney: true,
                range:[1,100000],
                messages: {
                    required: "请输入红包总数",
                    validateMoney: "请输入非零的正整数",
                    range: "输入值必须介于1和100000之间"
                }
            });
            $("#free_money").rules("add",{
                required: true,
                validateFloatMoney: true,
                range:[1,1000000],
                messages: {
                    required: "请输入红包总金额",
                    validateFloatMoney: "请输入非零的正整数,并且只允许一位小数",
                    range: "输入值必须介于1和1000000之间"
                }
            });

        }
        $('#type').val(obj.value);
    }

    /*jquery validate 规则*/
    $("#formId").validate({
        errorElement: 'form-group', //default input error message container
        errorClass: 'has-error', // default input error message class
        focusInvalid: false, // do not focus the last invalid input
        ignore: "",
        rules: {
            actname: {
                required : true,
                maxlength : 20
            },
            inviteContent: {
                required : true,
                maxlength : 10
            },
            single: {
                required : true,
                validateFloatMoney : true,
                maxlength : 10
            },
            total: {
                required : true,
                validateFloatMoney : true,
                maxlength : 10
            },
            wishing: {
                required : true,
                maxlength : 15
            },
            specifiedRequired: {
                required : true
            }

        },
        messages:{
            actname: {
                required : "请输入活动主题",
                maxlength : "最多为20个字"
            },
            inviteContent: {
                required : "请输入被邀请语",
                maxlength : "最多为10个字"
            },
            single: {
                required : "请输入单个红包金额",
                validateFloatMoney : "请输入非零的正整数,并且只允许一位小数",
                maxlength : "金额不能超过10位"
            },
            total: {
                required : "请输入总金额",
                validateFloatMoney : "请输入非零的正整数,并且只允许一位小数",
                maxlength : "金额不能超过10位"
            },
            wishing: {
                required : "请输入红包内容",
                maxlength : "最多为15个字"
            },
            specifiedRequired: {
                required : "请选择领取对象"
            }
        },
        highlight: function (element) { //hightlight error inputs
            $(element)
                    .closest('.has-error').removeClass('ok'); //display OK icon
            $(element)
                    .closest('.form-group').removeClass('success').addClass('has-error'); //set error class to the control group
        },
        unhighlight: function (element) { //revert the change dony by hightlight
            $(element)
                    .closest('.form-group').removeClass('has-error'); //set error class to the control group
        }
    });


    $(function(){

        $( "#start_data" ).val(new Date().Format('yyyy-MM-dd'));
        $( "#end_data" ).val(new Date().Format('yyyy-MM-dd'));

        $('#specifiedRequired').val('required');//校验领取对象标志

        $('#saveSubmit').on('click', function(){
            if(!$('#formId').valid()) return false;

            if(blessIndex == 0){
                var tmpId = 'bless_person_0';
                var content = $('#language_0').val();
                if(!bless_obj[tmpId]){
                    $.growl.error({ title: "错误", message: "请设置祝福人，填写祝福语"});
                    return false;
                }
                if(bless_obj[tmpId][0]['m_uid'] != '' && content != ''){
                    bless_obj[tmpId][0]['content'] = content;
                }else{
                    $.growl.error({ title: "错误", message: "请设置第1祝福人,填写祝福语"});
                    return false;
                }
            }


            //获取所有祝福语
            for(var i=0; i<blessIndex; i++){
                var tmpId = 'bless_person_'+i;
                var content = $('#language_'+i).val();
                if($('#' + tmpId).val() != undefined){
                    if(!bless_obj[tmpId]){
                        $.growl.error({ title: "错误", message: "请设置祝福人，填写祝福语"});
                        return false;
                    }
                    if(bless_obj[tmpId][0]['m_uid'] != '' && content != ''){
                        bless_obj[tmpId][0]['content'] = content;
                    }else{
                        $.growl.error({ title: "错误", message: "请设置第"+(i+1)+"祝福人,填写祝福语"});
                        return false;
                    }
                }
            }

            if($('#start_data').val() == ''){
                $.growl.error({ title: "错误", message: "请选择开始日期"});
                return false;
            }
            if($('#end_data').val() == ''){
                $.growl.error({ title: "错误", message: "请选择截止日期"});
                return false;
            }

            //领取对象
            var specifiedObj = {
                departments : dep_arr,
                users : user_arr
            };

            var startTime = $('#start_data').val()+' '+$('#start_time').val();
            var endTime = $('#end_data').val()+' '+$('#end_time').val();
            var start = new Date(startTime.replace(/-/g,"\/"));
            var end = new Date(endTime.replace(/-/g,"\/"));
            //截止时间必须大于开始时间
            if(end <= start){
                $.growl.error({ title: "错误", message: "截止时间必须大于开始时间"});
                return false;
            }

            var actname = $('#actname').val();//活动主题
            var inviteContent = $('#inviteContent').val();//被邀请语
            var type = $('#type').val();
            var total = $('#total').val();
            var wishing = $('#wishing').val();
            var allCompany = $('#allCompany').val();
            var imgReceiveBg = $('#imgReceiveBackgrund1').val();
            var imgChatBg = $('#imgChatBackgrund1').val();
            var single = $('#single').val();
            var freeSum = $('#free_sum').val();//自由红包总数
            var freeTotal = $('#free_money').val();//自由红包总金额


            var data = {
                actname : actname,
                inviteContent : inviteContent,
                type : type,
                single : single,
                total : total,
                wishing : wishing,
                allCompany : allCompany,
                startTime : startTime,
                endTime : endTime,
                imgReceiveBg : imgReceiveBg,
                imgChatBg : imgChatBg,
                blessHiddenObj : bless_obj,
                freeSum : freeSum,
                freeTotal : freeTotal,
                specifiedHiddenObj : specifiedObj

            };

            var btn = $('#saveSubmit').button('loading');
            $('#cancelId').attr('disabled', true);
            $.ajax({
                type:"post",
                url:"/BlessingRedpack/Apicp/BlessingRedpackCp/add",
                data:data,
                success: function(result) {
                    if(result.errcode == 0){
                        $('#modals-success').modal('show');
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
                    $('#cancelId').attr('disabled', false);
                    btn.button('reset');
                },
                error: function(result){
                    $('#cancelId').attr('disabled', false);
                    btn.button('reset');
                    $("#model_title").html('4300200');
                    $("#model_body").html('系统繁忙,请稍后再试');
                    $('#modals-error').modal('show');
                }
            });
        })
    });

    //模态框当调用 hide 实例方法时触发
    $('#modals-success').on('hide.bs.modal', function() {
        window.location.href = "/admincp/office/blessingredpack/list/pluginid/{$pluginId}/";
    });

    /*验证金额 非零正整数*/
    $.validator.methods.validateMoney= function(value, element, param){
        if(value=="")return true;
        return /^\+?[1-9][0-9]*$/.test(value);
    };

    /*验证金额 非零正整数,并且只能一位小数*/
    $.validator.methods.validateFloatMoney= function(value, element, param){
        if(value=="")return true;
        return /^[1-9]\d*([.][1-9])?$/.test(value);

    };

    // (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
    // (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
    Date.prototype.Format = function(fmt) {
        var o = {
            "M+" : this.getMonth()+1,                 //月份
            "d+" : this.getDate(),                    //日
            "h+" : this.getHours(),                   //小时
            "m+" : this.getMinutes(),                 //分
            "s+" : this.getSeconds(),                 //秒
            "q+" : Math.floor((this.getMonth()+3)/3), //季度
            "S"  : this.getMilliseconds()             //毫秒
        };
        if(/(y+)/.test(fmt))
            fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
        for(var k in o)
            if(new RegExp("("+ k +")").test(fmt))
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        return fmt;
    }


</script>




{include file="$tpl_dir_base/footer.tpl"}
