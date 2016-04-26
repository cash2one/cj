{include file='cyadmin/header.tpl'}
<script type="text/javascript" src="{$static_url}js/jqueryui.js"></script>
<script type="text/javascript" src="{$static_url}js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="{$static_url}js/jquery.iviewer.js"></script>
<script type="text/javascript">
var data = {
    lists: [], 
    last_end_id: 0, all_total: 0, append_total:0, viewer: null, lastdate: 0, request_limit: {$request_limit},
    blank_business_card: '{$static_url}images/blank-business-card.jpg'};
$(function() {
    data.viewer = $(".viewer").iviewer({
        onStartLoad: function (e, src) {
            $(".viewer").find('img').attr('src', '{$static_url}images/loading.gif');
        },
        src: '{$static_url}images/loading.gif'
    });

    update_status();
    
    $('.act-pull-next a').click(function () {
        if (data.lists.length == 0) {
            return false;
        }      
        var reason_type = $(this).attr('reason_type');
      
        $.ajax({
                url: "{$ajax_edit_url_base}",
                type: "POST",
                dataType: 'json',
                //async: false,
                data: 'act=pull_back&rnc_id='+data.lists[0].rnc_id+'&reason_type='+reason_type,
                success: function (results) {
                    data.lists.shift(0);
                    update_status();
                    
                }
            });
        return true;
       
    }); 
      
    $('input, textarea').click(function(){
        $(this).parents('.form-group').removeClass('has-error');
    });
    $('input, textarea').keydown(function(){
        $(this).parents('.form-group').removeClass('has-error');
    });
    $('#myform').submit(function () {  
        if (data.lists.length == 0) {
            return false;
        }      
        var has_error = false;
        $('#myform').find("input, textarea").each(function () {             
            if ($(this).attr('name') == 'info[realname]') {
                if ($(this).val() == '') {
                    has_error = true;
                    $(this).parents('.form-group').addClass('has-error');
                    
                }
            }
            if ($(this).attr('name') == 'info[mobilephone]') {
                if ($(this).val() == '') {
                    has_error = true;
                    $(this).parents('.form-group').addClass('has-error');
                }
            }
        });
        if (has_error) {
            return false;
        }
        var msg = '';
        $.ajax({
            url: "{$ajax_edit_url_base}",
            data: 'act=save&rnc_id='+data.lists[0].rnc_id+'&'+ $("input, textarea", this).serialize(),
            type: "post",
            async: false,
            success: function(msg){
                if (msg.status == 'no') {
                    msg = msg.msg;
                }
            }
        }); 
        if (msg) {
            alert(msg);
            
            return false;
        }
        data.lists.shift(0);
        update_status();
              
        return false;
    }); 
    
    $("input", $("#myform")).keydown(function (e) {
        if (e.keyCode == '13') {
            $("#myform").submit();
        }
     });    
});

function update_status() {
    $('#myform').find("input, textarea").each(function () {
        $(this).parents('.form-group').removeClass('has-error');
        $(this).val('');
    });
    
    if (data.lists.length < (data.request_limit/2)) {
        
        get_lists();
        if (data.all_total > 0) {
            //data.all_total = (data.all_total) -1;
        }
    } else {
        if (data.all_total > 0) {
            //data.all_total = (data.all_total) -1;
        }
    }
    next_pic();
    update_status_text();
}
function update_status_text()
{
    var append_text = '';
    if (data.append_total) {
        append_text = ', <strong>新增' + data.append_total + '张<strong>';
    }
    if (data.all_total > 0) {
        //data.all_total = data.all_total -1;
    }
    var num = data.all_total+data.lists.length;
    if (num > 0) {
        num = num -1;
    }
    //$('.notification-card .num').text(num);
    
    $('.status_text').html('截至' + data.lastdate + ' 还有' + (num) + '张名片待识别' + append_text);
}
function next_pic() {

        if (data.lists.length >= 1) {
            data.viewer.iviewer('loadImage', data.lists[0]._pictureurl);
        } else {
            data.viewer.iviewer('loadImage', data.blank_business_card);
        }
}
function get_lists() {
    var start = 0;
    if (data.lists.length) {
        start = data.lists[data.lists.length-1].rnc_id;
    }
    $.ajax({
                        url: "{$ajax_edit_url_base}",
                        type: "POST",
                        dataType: 'json',
                        data: 'act=get_lists&start='+start+'&last_end_id='+data.last_end_id,
                        async: false,
                        success: function (results) {
                            if (results.lists != null) {
                                $.each(results.lists, function (k, item) {
                                   data.lists[data.lists.length] = item;
                                });
                            }
                            data.all_total = parseInt(results.all_total);
                            data.append_total =  parseInt(results.append_total);
                            data.last_end_id = results.last_end_id;
                            data.lastdate = results.lastdate;
                        }
                    });
}

</script>


<table class="table  table-bordered">
    <tr>
        <td class="col-sm-4 ">
            <div class="viewer">
                <div class="col-sm-8 status_text"></div>
            
                 <div class="btn-group pull-right">
                 
                  <button type="button" class="btn btn-info dropdown-toggle " data-toggle="dropdown">
                    驳回 (显示下一张) <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu act-pull-next" role="menu">
                    <li><a href="#" reason_type="noclear">图片不清晰</a></li>
                    <li><a href="#" reason_type="nocard">图片类型不符合</a></li>
                  </ul>
                </div>
            </div>
        </td>
        <td class="col-sm-4 wrapper">
           <form class="form-horizontal" id="myform" role="form">
              <div class="form-group">
                <label class="col-sm-2 control-label">姓名</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[realname]" required="required" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">手机</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[mobilephone]" required="required" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">座机</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[telephone]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">邮箱</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[email]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">公司</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[company]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">部门</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[department]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">职位</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[job]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">地址</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[address]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">邮编</label>
                <div class="col-sm-7">
                  <input type="text" class="form-control" name="info[postcode]">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">其它</label>
                <div class="col-sm-7">
                  <textarea class="form-control" name="info[other]"></textarea>
                </div>
              </div>
              
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10 btn-group-rec">
                   
                    <span>
                    <button type="submit" class="btn  btn-primary">提交 (显示下一张)</button>
                    </span>
                </div>
              </div>
            </form>
      </td>
    </tr>
</table>
{include file='cyadmin/footer.tpl'}