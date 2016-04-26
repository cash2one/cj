{include file="$tpl_dir_base/header.tpl" }

<div class="panel panel-default font12">
    <div class="panel-body">
        <div class="profile-row">
	        <div class="left-col left-col-more">
		        <div class="profile-block">
			        <div class="panel profile-photo photo-margin">
				        {foreach $news as $_id => $_data}
					        {if $_data@index eq 0}
						        <div class="img-box img-box-more dbclick news-thumbnail {if $_data['ne_id'] eq $ne_id}selected{/if}" id="fill_cover">
							        <div class="img-text"><img src="{$_data['cover']['url']}"></div>
							        <div class="img-title">{$_data['title']}</div>
						        </div>
					        {elseif $_data@index eq 1}
						        <div class="title-position news-thumbnail {if $_data['ne_id'] eq $ne_id}selected{/if}">
							        <span class="panel-title">{$_data['title']}</span>
							        <span class="showimage title-thumbnail"><img src="{$_data['cover']['url']}"></span>
						        </div>
					        {else}
						        <div class="title-position news-thumbnail del {if $_data['ne_id'] eq $ne_id}selected{/if}">
							        <a class="text-danger delete" data-index="{$_data@index}" href="javascript:void(0);" style="display: none;">
								        <i class="fa fa-times"></i>
							        </a>
							        <span class="panel-title">{$_data['title']}</span>
									<span class="showimage title-thumbnail"><img src="{$_data['cover']['url']}"></span>
						        </div>
					        {/if}

				        {/foreach}
			        </div>
		        </div>
		        <div id="last" class="added">
			        <i class="fa fa-plus"></i>
			        添加一条公告
		        </div>
	        </div>
            <div class="right-col">
            <div class="panel tl-body">
                <form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" id="post_from">
                        <input type="hidden" name="formhash" value="{$formhash}" />
                        <input type="hidden" name="ne_id" value="{$news_first['ne_id']}" />
                        <input type="hidden" name="multiple" value="{$news_first['multiple']}" />
                            <div class="form-group">
                                <label class="control-label col-sm-2 " for="id_title">标题*</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-small" id="id_title" name="title" placeholder="最多输入64个字符" value="{$news_first['title']|escape}" maxlength="64"  required="required"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="">作者*</label>
                                <div class="col-sm-9">
                                    {include
                                    file="$tpl_dir_base/common_selector_member.tpl"
                                    input_type='radio'
                                    input_name='author'
                                    selector_box_id='author'
                                    allow_member=true
                                    allow_department=false
                                    default_data = $default_author
                                    }
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2 " for="id_title">摘要</label>
                                <div class="col-sm-9">
                                    <textarea  class="form-control form-small" id="id_summary" name="summary" placeholder="最多输入120个字符" maxlength="120" rows=4>{$news_first['summary']|escape}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                            <label class="control-label col-sm-2 " for="id_label_tc_id">公告类型*</label>
                            <span class="space"></span>
                            <div class="col-sm-9">
                                <select id="id_nca_id" name="nca_id" class="form-control form-small" data-width="auto"  required="required">
                                    <option value="" selected="selected">请选择类型*</option>
                                    {foreach $categories as $_key => $_val}
                                    <optgroup label="{$_val['name']}">
                                        {if isset($_val['nodes'])}
                                            {foreach $_val['nodes'] as $_sv}
                                                <option value="{$_sv['nca_id']}" {if $news_first['nca_id'] == $_sv['nca_id']} selected="selected"{/if}>{$_sv['name']}</option>
                                            {/foreach}
                                        {else}
                                            <option value="{$_val['nca_id']}" {if $news_first['nca_id'] == $_val['nca_id']} selected="selected"{/if}>{$_val['name']}</option>
                                        {/if}
                                    </optgroup>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                            
                            <div class="form-group">
                                <label class="control-label  col-sm-2 " for="id_author">消息保密</label>
                                <div class="col-sm-2">
                                    <input type="radio" class=" form-small" id="is_secret" name="is_secret" value="0" {if $news_first['is_secret'] == 0}checked{/if} />&nbsp;&nbsp;关闭
                                </div>
                                <div class="col-sm-3">
                                    <input type="radio" class=" form-small" id="is_secret" name="is_secret" value="1" {if $news_first['is_secret'] == 1}checked{/if}/>&nbsp;&nbsp;开启
                                </div>
                                <div class="col-sm-4">
                                    <a class="" data-toggle="modal" data-target="#mymessage">什么是消息保密？</a>
                                </div>
                                <div id="mymessage" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h4 class="modal-title" id="myModalLabel">什么是消息保密？</h4>
                                            </div>
                                            <div class="modal-body">
                                                <!-- <h4>什么是消息保密？</h4> -->
                                                <p>
        
                                                    1.消息保密开启后，这条消息或公告将无法分享到朋友圈；<br>
        
                                                    2.消息保密开启后，用户在微信端收到的消息详情页将会加上姓名水印，一定程度上防止客户用手机“截屏”泄密。
                                                                    <br>
                                                                    例如：
                                                </p>
                                                <p><center>
                                                    <img src="/admincp/static/images/weixin_pic.gif">
                                                </center></p>
                                                
                                            </div>
                                            <!-- / .modal-body -->
                                            <div class="modal-footer text-center">
                                                <center><button type="button" class="btn btn-default btn-lg" data-dismiss="modal">知道了</button></center>
                                            </div>
                                        </div>
                                        <!-- / .modal-content -->
                                    </div>
                                    <!-- / .modal-dialog -->
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label  col-sm-2 " for="id_author">封面图片*</label>
                                <div class="col-sm-9">
                                    {cycp_upload
                                        inputname='cover_id'
                                        attachid = $news_first['cover_id']
                                        callback='fill_cover'
                                        hidedelete=1
                                        tip='(推荐尺寸 480x230)'
                                    }
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="id_rights">阅读权限*</label>
                                <div class="col-sm-9">
                                    <div>
                                        <input type="hidden" class="form-small" id="is_all" name="is_all" value="{$news_first['is_all']}"/>
                                        <button type="button" class="btn {if $news_first['is_all'] == 1}btn-primary{/if}" id="all_btn">全公司</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <button type="button" class="btn {if $news_first['is_all'] == 0}btn-primary{/if}" id="specified_btn">指定对象</button>
                                        <span class="is_push_box">
                                            <label class="radio-inline">
                                            <input type="checkbox" class=" form-small" id="is_push" name="is_push" value="1" checked/>
                                            &nbsp;&nbsp;消息推送
                                        </label>
                                    </span>
                                    </div>
                                    <div id="user_dep_container" {if $news_first['is_all'] == 1}style="display: none" {/if}>
                                        <hr>
                                        <div class="row">
                                            <label class="col-sm-3 text-right padding-sm">选择部门：</label>

                                            <div id="deps_container" class="col-sm-8">
                                                {include 
                                                    file="$tpl_dir_base/common_selector_member.tpl"
                                                    input_type='checkbox'
                                                    input_name_department='cd_ids[]'
                                                    selector_box_id='deps_container'
                                                    allow_member=false
                                                    allow_department=true
                                                    default_data = $default_departments
                                                }   
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <label class="col-sm-3 text-right padding-sm">选择人员：</label>
                                            <div id="users_container" class="col-sm-8">
                                                {include 
                                                    file="$tpl_dir_base/common_selector_member.tpl"
                                                    input_type='checkbox'
                                                    input_name='m_uids[]'
                                                    selector_box_id='users_container'
                                                    allow_member=true
                                                    allow_department=false
                                                    default_data = $default_users
                                                }   
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-left: 25px">
                                <div class="col-sm-11">
                                    {$ueditor_output}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3 col-sm-offset-2">
                                    <input type="checkbox" class=" form-small"  name="is_comment" value="1"{if $news_first['is_comment'] == 1}checked{/if}/>&nbsp;&nbsp;开启评论功能
                                </div>
                                <div class="col-sm-3 col-sm-offset-2">
                                    <input type="checkbox" class="form-small"  name="is_like" value="1" {if $news_first['is_like'] == 1}checked{/if}/>&nbsp;&nbsp;开启点赞功能
                            </div>
                            </div>
                        {if $news_first['is_publish'] != 1}
                            <div class="form-group">
                                <div class="col-sm-3 col-sm-offset-2">
                                    <label for="is_check">
                                        <input type="checkbox" class="form-small" id="is_check"  name="is_check" value="1" />
                                        &nbsp;&nbsp;开启预览功能
                                    </label>
                                </div>
                            </div>
                                <div class="check">
                                    <div class="from-group">
                                        <label class="control-label col-sm-2 " for="check_summary">预览说明</label>
                                        <div class="col-sm-9">
                                            <textarea  class="form-control form-small news-form" id="check_summary" name="check_summary" placeholder="最多输入120个字符" maxlength="120" rows=4></textarea>
                                        </div>
                                    </div>
                                    <div class="from-group">
                                        <label class="control-label col-sm-2 " for="users_check">预览人</label>
                                        <div class="col-sm-9">
                                            {include
                                            file="$tpl_dir_base/common_selector_member.tpl"
                                            input_type='checkbox'
                                            input_name='check_id[]'
                                            selector_box_id='users_check'
                                            allow_member=true
                                            allow_department=false
                                            }
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-9">
                                            <div class="row">
                                                <div class="col-md-4">
	                                                <input type="hidden" class="form-small" id="is_publish" name="is_publish" value="0"/>
	                                                <button type="button" class="btn btn-primary col-md-9" style="display:none" data-toggle="modal" id="preview_btn">提交预览</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        {/if}
                        <div class="form-group" id="btn-box">
                            <div class="col-sm-offset-2 col-sm-9">
                                <div class="row">
                                    {if $news_first['is_publish'] == 0}
                                    <div class="col-md-4">
	                                    <button type="button" class="btn btn-primary  col-md-9" id="draft_btn">保存草稿</button>
                                    </div>
                                    {/if}
                                    <div class="col-md-4">
	                                    <button type="button" class="btn btn-primary col-md-9" id="publish_btn">发布</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="form-small" id="is_publish" name="is_publish" value="0"/>
                    </form>
            </div>
        </div>
        </div>
    </div>
</div>


<script type="text/javascript">
	//数据保存基类
	var news = {$news_json};

	$(function(){
		console.log(news);
		var navActive = $(".nav li a:contains('添加公告')").parent('li');
		navActive.addClass('active');
		//添加新的公告
		$('.added').on('click', function(){
			var news_thumbnail = $('.news-thumbnail');
			var _string = '';
			var _news_gallery = news_thumbnail.length; //公告数展示
			if(news.length > 8) {
				alert('最多添加10条数据!');
				return false;
			}
			//integrate(); //创建数据字典
			var selected = $('.selected').index('.news-thumbnail');
			console.log('当前选中项:'+selected)
			if(news.length < (selected + 1)) {
				//空的时候进行数据插入 或 当前未保存
				integrate();
			} else {
				updated(selected);
			}
			news_thumbnail.removeClass("selected");
			$(".news-thumbnail:eq("+news.length+")").addClass("selected");

			if(news.length > 1) {
				_string +='<div class="title-position news-thumbnail del selected" >';
				_string += '<a href="javascript:void(0);" class="text-danger delete" data-index="'+_news_gallery+'"><i class="fa fa-times"></i></a>'
				_string +='<span class="panel-title">标题</span><span class="showimage title-thumbnail">封面<br/>图片</span></div>';
				$('.profile-photo').append(_string);
			}
			data_ins();
			if(news.length < _news_gallery) {
				//空的时候进行数据插入 或 当前未保存
				integrate();
			}
			console.log(news);
		});

		var newtitlebox = $('.profile-photo');
		//显示删除按钮
		newtitlebox.on('mouseover', '.del', function(){
			$(this).children('.delete').show();
		});
		//隐藏删除按钮
		newtitlebox.on('mouseout', '.del', function () {
			$(this).children('.delete').hide();
		});
		newtitlebox.on('click', 'a', function (event) {
			//以保存的公告数
			var _index = news.length;
			//展示的公告数
			var selected = $('.selected').index('.news-thumbnail'); //选中
			var _news_gallery = $('.news-thumbnail').length;
			var news_index = $(this).data('index');
			$(this).parent('.del').remove();
			news.splice(news_index, 1);
			if(selected == news_index){
				var news_thumbnail = $('.news-thumbnail');
				news_thumbnail.eq(0).addClass("selected");
				data_upd(0);
			}
			console.log(news_index);
			console.log(selected);
			console.log(news);
			event.stopPropagation(); //阻止事件冒泡
		});

		//修改数据
		newtitlebox.on('click', '.news-thumbnail', function() {
			var news_thumbnail = $('.news-thumbnail');
			var selected = $('.selected').index('.news-thumbnail');
			var index = $(this).index();

			if(news.length < (selected + 1)) {
				//空的时候进行数据插入 或 当前未保存
				integrate();
			} else {
				updated(selected);
			}
			console.log('数据长度'+news.length);
			console.log('选择索引'+selected);
			console.log('选择索引2'+index);
			/*if(news.length < selected) {
			 data_ins();
			 } else {
			 data_upd(index);

			 }*/
			news_thumbnail.removeClass("selected");
			$(this).addClass("selected");
			var now_selected = $('.selected').index('.news-thumbnail');
			if(news.length < (now_selected + 1)) {
				//空的时候进行数据插入 或 当前未保存
				data_ins();
				integrate();
			}else {
				data_upd(now_selected);
			}
			console.log(news);
		});

		//当阅读对象为指定对象时
		$('#specified_btn').bind('click',function(){
			$('#user_dep_container').show();
			$(this).addClass('btn-primary');
			$('#all_btn').removeClass('btn-primary');
			$('#is_all').val(0);
		});
		//当阅读对象为全公司时
		$('#all_btn').bind('click', function(){
			$('#user_dep_container').hide();
			$(this).addClass('btn-primary');
			$('#specified_btn').removeClass('btn-primary');
			$('#is_all').val(1);
		});

		//开启/关闭审批推送
		var check_check = $('#is_check');
		check_check.on('click', function() {
			if(check_check.is(':checked')){
				$('.check').show();
				$('#preview_btn').show();
				$('#btn-box').hide();
			}else{
				$('.check').hide();
				$('#preview_btn').hide();
				$('#btn-box').show();
			}

		});


		//当点击保存草稿时
		$('#draft_btn').bind('click', function(){
			if(news.length < 1) {
				alert('公告未编辑数少于2条!');
				return false;
			}
			var selected = $('.selected').index('.news-thumbnail');
			updated(selected);

			var my_data_index = false;
			var my_data_error = '';
			for(var i = 0, max = news.length; i < max ; i++ ){
				if (news[i].title == '') {
					my_data_index = i;
					my_data_error = '请输入标题！';
					console.log('输出'+i);
					break;
				}
				if (news[i].cover_id == '') {
					my_data_index = i;
					my_data_error = '请选择封面图片！';
					console.log('输出'+i);
					break;
				}
				if (news[i].nca_id == '') {
					my_data_index = i;
					my_data_error = '请选择类型！';
					console.log('输出'+i);
					break;
				}
				if (news[i].content == '') {
					my_data_index = i;
					my_data_error = '请输入正文！';
					console.log('输出'+i);
					break;
				}
				console.log('不输出'+i);
			}
			switch (my_data_index) {
				case false:
					break;
				default :
					var news_thumbnail = $('.news-thumbnail');
					news_thumbnail.removeClass("selected");
					news_thumbnail.eq(my_data_index).addClass("selected");
					data_upd(my_data_index);
					console.log(my_data_index);
					alert(my_data_error);
					return false;
					break;
			}
			var author = $('input[name="author"]').val();
			if(typeof(author) == 'undefined') {
				alert('请选择作者！');
				return false;
			}
			var _cd_ids = '',_m_uids = '';
			var _is_all = $('input[name="is_all"]').val(); //阅读权限
			if(_is_all == '1') {
				_cd_ids = 0;
				_m_uids = 0;
			} else {
				$('input[name="cd_ids[]"]').each(function(){
					var value = $(this).val();
					_cd_ids += value + ',';
				})
				$('input[name="m_uids[]"]').each(function(){
					var value = $(this).val();
					_m_uids += $(this).val() + ',';
				})
			}
			var is_push = $('input[name="is_push"]:checked').val();
			var multiple = $('input[name="multiple"]').val();
			$.ajax({
				type : 'POST',
				url : '{$api_action_url}',
				data : {
					'mydata': news,
					'is_publish': '0',
					'is_check': '0',
					'author':author,
					'multiple':multiple,
					'is_all':_is_all,
					'is_push':is_push,
					'cd_ids':_cd_ids,
					'm_uids':_m_uids
				},
				success: function(data){
					if(data.errcode == '0') {
						alert('保存草稿成功！');
						window.location.href = data.result.url;
					}
				}
			});
		});
		//当点击发布时
		$('#publish_btn').bind('click', function(){

			if(news.length < 1) {
				alert('公告未编辑数少于2条!');
				return false;
			}
			var selected = $('.selected').index('.news-thumbnail');
			console.log('发布公告:'+selected);
			updated(selected);

			var my_data_index = false;
			var my_data_error = '';
			for(var i = 0, max = news.length; i < max ; i++ ){
				if (news[i].title == '') {
					my_data_index = i;
					my_data_error = '请输入标题！';
					console.log('输出'+i);
					break;
				}
				if (news[i].cover_id == '') {
					my_data_index = i;
					my_data_error = '请选择封面图片！';
					console.log('输出'+i);
					break;
				}
				if (news[i].nca_id == '') {
					my_data_index = i;
					my_data_error = '请选择类型！';
					console.log('输出'+i);
					break;
				}
				if (news[i].content == '') {
					my_data_index = i;
					my_data_error = '请输入正文！';
					console.log('输出'+i);
					break;
				}
				console.log('不输出'+i);
			}
			switch (my_data_index) {
				case false:
					break;
				default :
					var news_thumbnail = $('.news-thumbnail');
					news_thumbnail.removeClass("selected");
					news_thumbnail.eq(my_data_index).addClass("selected");
					data_upd(my_data_index);
					console.log(my_data_index);
					alert(my_data_error);
					return false;
					break;
			}
			var author = $('input[name="author"]').val();
			if(typeof(author) == 'undefined') {
				alert('请选择作者！');
				return false;
			}

			var _cd_ids = '',_m_uids = '';
			var _is_all = $('input[name="is_all"]').val(); //阅读权限
			if(_is_all == '1') {
				_cd_ids = 0;
				_m_uids = 0;
			} else {
				$('input[name="cd_ids[]"]').each(function(){
					var value = $(this).val();
					_cd_ids += value + ',';
				})
				$('input[name="m_uids[]"]').each(function(){
					var value = $(this).val();
					_m_uids += $(this).val() + ',';
				})
			}
			$('#is_publish').val(1);
			var is_push = $('input[name="is_push"]:checked').val();
			var multiple = $('input[name="multiple"]').val();
			$.ajax({
				type : 'POST',
				url : '{$api_action_url}',
				data : {
					'mydata': news,
					'is_publish': '1',
					'is_check': '0',
					'author':author,
					'multiple':multiple,
					'is_all':_is_all,
					'is_push':is_push,
					'cd_ids':_cd_ids,
					'm_uids':_m_uids
				},
				success: function(data){
					if(data.errcode == '0') {

						//alert('发布成功！');
						window.location.href = data.result.url;
					}
				}
			});
		});
		//审核发布
		$("#preview_btn").on('click', function(){

			if(news.length < 1) {
				alert('公告未编辑数少于2条!');
				return false;
			}
			var selected = $('.selected').index('.news-thumbnail');
			console.log('发布公告:'+selected);
			updated(selected);

			var my_data_index = false;
			var my_data_error = '';
			for(var i = 0, max = news.length; i < max ; i++ ){
				if (news[i].title == '') {
					my_data_index = i;
					my_data_error = '请输入标题！';
					console.log('输出b'+i);
					break;
				}
				if (news[i].cover_id == '') {
					my_data_index = i;
					my_data_error = '请选择封面图片！';
					console.log('输出t'+i);
					break;
				}
				if (news[i].nca_id == '') {
					my_data_index = i;
					my_data_error = '请选择类型！';
					console.log('输出l'+i);
					break;
				}
				if (news[i].content == '') {
					my_data_index = i;
					my_data_error = '请输入正文！';
					console.log('输出z'+i);
					break;
				}
				console.log('不输出'+i);
			}
			switch (my_data_index) {
				case false:
					break;
				default :
					var news_thumbnail = $('.news-thumbnail');
					news_thumbnail.removeClass("selected");
					news_thumbnail.eq(my_data_index).addClass("selected");
					data_upd(my_data_index);
					console.log(my_data_index);
					alert(my_data_error);
					return false;
					break;
			}
			var author = $('input[name="author"]').val();
			if(typeof(author) == 'undefined') {
				alert('请选择作者！');
				return false;
			}
			var _person = '';
			$('input[name="check_id[]"]').each(function(){
				_person += $(this).val() + ',';
			})
			var _check_summary = $('textarea[name="check_summary"]').val();
			var _is_publish = 0;
			if (_person == '' || _check_summary == '') {
				alert('请选择审批人！');
				return false;
			}
			var _cd_ids = '',_m_uids = '';
			var _is_all = $('input[name="is_all"]').val(); //阅读权限
			if(_is_all == '1') {
				_cd_ids = 0;
				_m_uids = 0;
			} else {
				$('input[name="cd_ids[]"]').each(function(){
					var value = $(this).val();
					_cd_ids += value + ',';
				})
				$('input[name="m_uids[]"]').each(function(){
					var value = $(this).val();
					_m_uids += $(this).val() + ',';
				})
			}
			var is_push = $('input[name="is_push"]:checked').val();
			var multiple = $('input[name="multiple"]').val();
			$.ajax({
				type : 'POST',
				url : '{$api_action_url}',
				data : {
					'mydata': news,
					'is_publish': _is_publish,
					'check_id': _person,
					'check_summary': _check_summary,
					'is_check': '1',
					'author': author,
					'multiple': multiple,
					'is_all': _is_all,
					'is_push':is_push,
					'cd_ids': _cd_ids,
					'm_uids': _m_uids
				},
				success: function(data){
					if(data.errcode == '0') {
						alert('发送预览成功！');
						window.location.href = data.result.url;
					}
				}
			});
		})
		//预览保存 end
	});
	//模板-数据清空
	function data_ins() {
		var redio_secred = $('input[name="is_secret"]');
		$('input[name="ne_id"]').val('');//
		$('input[name="title"]').val(''); //标题
		$('textarea[name="summary"]').val(''); //摘要
		$('select[name="nca_id"]').get(0).options[0].selected = true;//类型
		redio_secred.eq(0).prop("checked", true);
		$('input[name="cover_id"]').val('');//封面图片
		$('._showimage').empty();
		ue.setContent('');
		$('input[name="is_comment"]').prop("checked", false);
		$('input[name="is_like"]').prop("checked", false);
	}
	//模板数据-迭代
	function data_upd(index) {
		var newsdata = news[index];
		$('input[name="title"]').val(newsdata.title); //标题
		$('input[name="ne_id"]').val(newsdata.ne_id); //编辑--id
		$('textarea[name="summary"]').val(newsdata.summary); //摘要
		//$('select[name="nca_id"] option:selected').val();//类型
		var count = $('select[name="nca_id"] option').length;
		for (var i = 0; i < count; i++) {
			if ($('select[name="nca_id"]').get(0).options[i].value == newsdata.nca_id) {
				$('select[name="nca_id"]').get(0).options[i].selected = true;
				break;
			}
		}
		var redio_secred = $('input[name="is_secret"]');
		if (newsdata.is_secret == 1) {
			console.log('保密');
			redio_secred.eq(1).prop("checked", true);//消息保密
		} else {
			console.log('不保密');
			redio_secred.eq(0).prop("checked", true);
		}
		$('._showimage').empty();
		if(newsdata.cover_id != '') {
			$('input[name="cover_id"]').val(newsdata.cover_id);//封面图片
			$('._showimage').html('<a href="http://' + window.location.host + '/attachment/read/' + newsdata.cover_id + '" target="_blank"><img src="http://' + window.location.host + '/attachment/read/' + newsdata.cover_id + '/45" border="0" style="max-width:64px;max-height:32px"></a>');
		} else {
			$('input[name="cover_id"]').val('');//封面图片
		}
		ue.setContent(newsdata.content);
		if (newsdata.is_comment == 1) {
			$('input[name="is_comment"]').prop("checked", true);
		}else{
			$('input[name="is_comment"]').prop("checked", false);
		}
		if (newsdata.is_like == 1) {
			$('input[name="is_like"]').prop("checked", true);
		}else{
			$('input[name="is_like"]').prop("checked", false);
		}
	}


	//数据整合--插入
	function integrate() {

		//当前类型数据集合
		var _dataset = {};
		var _title = $('input[name="title"]').val(); //标题
		var _summary = $('textarea[name="summary"]').val(); //摘要
		var _nca_id = $('select[name="nca_id"] option:selected').val();//类型
		var _is_secret = $('input[name="is_secret"]:checked').val(); //消息保密
		var _cover_id = $('input[name="cover_id"]').val();//封面图片
		var _content = ue.getContent();
		var _is_comment = $('input[name="is_comment"]:checked').val();//开启评论
		var _is_like = $('input[name="is_like"]:checked').val();//开启点赞

		_dataset = {
			'title':_title,
			'summary':_summary,
			'nca_id':_nca_id,
			'is_secret':_is_secret,
			'cover_id':_cover_id,
			'content':_content,
			'is_comment':_is_comment,
			'is_like':_is_like
		}
		//console.log(_dataset);
		//console.log(news[news.length-1] );

		news.push(_dataset); //插入数据集

		return true;
	}
	//数据整合--修改
	function updated(selected) {

		//当前类型数据集合
		//当前类型数据集合
		var _dataset = {};
		var _title = $('input[name="title"]').val(); //标题
		var _summary = $('textarea[name="summary"]').val(); //摘要
		var _nca_id = $('select[name="nca_id"] option:selected').val();//类型
		var _is_secret = $('input[name="is_secret"]:checked').val(); //消息保密
		var _cover_id = $('input[name="cover_id"]').val();//封面图片
		var _content = ue.getContent();
		var _is_comment = $('input[name="is_comment"]:checked').val();//开启评论
		var _is_like = $('input[name="is_like"]:checked').val();//开启点赞
		var _id = $('input[name="ne_id"]').val(); //标题
		var _multiple = $('input[name="multiple"]').val(); //标题

		_dataset = {
			'ne_id':_id,
			'multiple':_multiple,
			'title':_title,
			'summary':_summary,
			'nca_id':_nca_id,
			'is_secret':_is_secret,
			'cover_id':_cover_id,
			'content':_content,
			'is_comment':_is_comment,
			'is_like':_is_like
		}
		//console.log(_dataset);
		//console.log(news[news.length-1] );

		news[selected] = _dataset ; //插入数据集

		return true;
	}
	//填充标题
	$('#id_title').bind('blur', function(){
		var title = $(this).val();
		//判断当前的指针
		var selected = $('.selected').index('.news-thumbnail');
		switch(selected) {
			case 0:
				$('.img-title').html(title);
				break;
			default:
				$('.news-thumbnail').eq(selected).children('.panel-title').html(title);
		}
		console.log(selected);
	});
	//填充封面
	function fill_cover(result){
		var url = result.list[0].url;
		//判断当前的指针
		var selected = $('.selected').index('.news-thumbnail');
		switch(selected) {
			case 0:
				$('.img-text').html('<img src="'+url+'"/>');
				break;
			default:
				$('.news-thumbnail').eq(selected).children('.title-thumbnail').html('<img src="'+url+'"/>');
		}

	}
</script>

{include file="$tpl_dir_base/footer.tpl"}