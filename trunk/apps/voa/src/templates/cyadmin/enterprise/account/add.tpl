{include file='cyadmin/header.tpl'}
<script>
	$(function(){
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
			});
		});
</script>
    <h3>
添加代理:  <a href='{$list_url_base}' class="badge pull-right" style="margin-left:20px;padding:5px 10px;margin-right:20px;">代理列表</a>&nbsp;&nbsp;
    </h3>
    <form action="{$form_url}" method="post" id="addform">
  

<div id="form-adminer-edit" class="form-horizontal font12" style="border:1px solid #CCC">
	<div class="form-group">
		<label class="col-sm-2 control-label">代理区域：</label>
		<div class="col-sm-6">
			<p class="form-control-static">
			<input type="text" name="province" placeholder="请输入省份" id="province">
			<input type="text" name="city" placeholder="请输入城市" id="city">
			<input type="text" name="county" placeholder="请输入街道/区" id="county">
			</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">公司名称：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><input id="co_name" type="text" name="co_name" placeholder="输入公司名称"></p>
		</div>
	</div>
	<div class="form-group">
	<label class="col-sm-2 control-label">简介：</label>
	<div class="col-sm-6">
		<p class="form-control-static">
		<textarea name="intro" cols=75 rows=5 id="intro"></textarea>
		</p>
	</div>
    </div>
    <div class="form-group">
	    <label class="col-sm-2 control-label">联系人姓名：</label>
	    <div class="col-sm-6">
		    <p class="form-control-static"><input type="text" name="link_name" id="link_name"></p>
	    </div>
    </div>
    <div class="form-group">
		<label class="col-sm-2 control-label">联系人手机：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><input type="text" name="link_phone" id="link_phone"></p>
		</div>
	</div>
	<div class="form-group">
    	<label class="col-sm-2 control-label">代理期限：</label>
    	<div class="col-sm-6">
    		<p class="form-control-static"><select name="deadline">
    		<option >请选择...</option>
    		<option value="1">1年</option>
    		<option value="2">2年</option>
    		<option value="3">3年</option>
    		</select></p>
    	</div>
    </div>
    <div class="form-group">
    	<label class="col-sm-2 control-label">注册时间：</label>
    	<!-- 
    	<div class="col-sm-6">
    		<p class="form-control-static"><input type="date" name="created_day"><input type="time" name="created_hour"></p>
    	</div>
    	 -->
    	  <div class="col-md-4" id="sandbox-container">
            <div class="input-daterange input-group" id="datepicker" style="float:left;">
            <input type="text" class="input-sm form-control" placeholder="选择时间" name="created_day">
          
            </div>
            &nbsp;
            <div style="clear:both;float:left"></div><input  class="input-sm" type="time" name="created_hour" ></div>
        </div>
    </div>
    <br>
		<input type="submit" value="确定" >
</div>



</form>
<script>
	$(function(){
		$('#addform').submit(function(){
			var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
			var phone = /^[0-9]+$/gi;
				if($('#province').val().length >12){
					alert('长度过长');
					return false;
				}
				//只能输入汉字数字和英文字母
				if($('#province').val() !=''){		
				if (!re.test($('#province').val())) {							
					alert('输入省份含非法字符');
					return false;
				}
				}
				if($('#city').val().length >12){
					alert('长度过长');
					return false;
				}
				var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
				if($('#city').val() !=''){		
				if (!re.test($('#city').val())) {							
					alert('输入城市含非法字符');
					return false;
				}
				}
				if($('#county').val().length >12){
					alert('长度过长');
					return false;
				}
				var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
				if($('#county').val() !=''){		
				if (!re.test($('#county').val())) {							
					alert('输入含非法字符');
					return false;
				}
				}
				if($('#co_name').val().length >12){
					alert('名称长度过长');
					return false;
					}
				if($('#co_name').val() ==''){
					alert('请填写公司名称');
					return false;
					}
				var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
				if (!re.test($('#co_name').val())) {							
					alert('输入内容含非法字符');
					return false;
				}
				
				if($('#intro').val().length>500){
					alert('简介字数超过限制');
					return false;
					}
				if($('#intro').val() ==''){
					alert('请填写公司简介');
					return false;
					}
				var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
				if (!re.test($('#intro').val())) {							
					alert('输入内容含非法字符');
					return false;
				}
				if($('#link_name').val()==''){
					alert('请填写联系人姓名');
					return false;
					}
				if($('#link_name').val().length>5){
					alert('联系人姓名过长');
					return false;
					}
				var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
				if (!re.test($('#link_name').val())) {							
					alert('输入内容含非法字符');
					return false;
				}
				
				if($('#link_phone').val()==''){
					alert('请填写手机号码');
					return false;
					}
				if($('#link_phone').val().length !=11 ){
					alert('请输入正确的手机号码');
					return false;
					}
		
				
				var re = /^[0-9]+$/gi;
				if (!re.test($('#link_phone').val())) {							
					alert('请输入正确的手机号码');
					return false;
				}
		
			});
		});
</script>

{include file='cyadmin/footer.tpl'}