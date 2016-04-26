{include file='mobile/header.tpl' css_file='app_news.css'}
<!-- <Img src="{$IMGDIR}like.png" /> -->
<div id="oa-xw-veiw">
	<div class="ui-main-content ui-list news-content clearfix" >
        <div class="ui-top-content">
            <h2>{$news['title']|rhtmlspecialchars}</h2>
            <p class="other clearfix">
                <span class="author">发起人:{$news['author']}</span>
                <span class="time">{$news['published']}</span>
                <span class="visit">浏览 ({$news['read_num']})</span>
            </p>
        </div>
        <div {if $news['is_secret'] == 1}style="background-image: url(/frontend/news/watermark);"{/if} class="news-content-watermark" style="width: 100%; position: absolute;z-index:9999"></div>
        {if $news['is_secret'] == 1}
            {cyoa_wxqymenu
                type='hide'
            }
        {/if}
        {$news['content']}

        <!--点赞-->
        {if $news['is_like'] == 1 && $news['is_publish'] == 1}
            <div style="display:none;" data-like="{$news['is_like']}" id="data-like"></div>
            <input type="hidden" id="ne_id" name="ne_id" value="{$news['ne_id']}">
            <div class="ui-txt" style="position: relative;">
                <div class="is-like" style="color:#666;">
                    {*修改当前布局样式*}
                    {if $news['new_des'] == 2}
                        <div class="circulars" id="like" data-like="1">
                            <span id="text_like">{$news['num_like']}</span>
                        </div>
                    {else}
                        <div class="circular" id="like" data-like="1">
                            <span id="text_like">{$news['num_like']}</span>
                        </div>
                    {/if}


                </div>

            </div>

        {literal}
            <script type="text/javascript">

                require(["zepto", "underscore", "submit", "frozen"], function($, _, submit) {
                    $('#like').on('click',function(){
                        var ne_id = $('#ne_id').val();

                        var is_like = $('#data-like').attr('data-like');
                        // var description = $("#like").attr("data-like");
                        var like_cla = $('#like').attr('class');
                        $.ajax({
                            'type': 'POST',
                            'url': '/api/news/post/like',
                            //'data': {ne_id: ne_id,description:description,is_like:is_like},
                            'data': {ne_id: ne_id,is_like:is_like},
                            beforeSend: function () {
                                el = $.loading({content: '正在点赞...'})
                            },
                            success: function(data){
                                el.loading('hide');
                                if (data.errcode==0) {
                                    // console.log(data);
                                    $('#text_like').text(data.result.like.num_like);
                                    if(like_cla == "circular"){
                                        $('#like').attr('class','circulars');
                                    }else if(like_cla == "circulars"){
                                        $('#like').attr('class','circular');
                                    }
                                }else{
                                    $.tips({content:data.errmsg,stayTime:2000,type:"warn"})
                                }
                            },
                            error: function(){
                                el.loading('hide');
                            }
                        });

                    });
                });

            </script>
        {/literal}
        {/if}
	</div>

	<!--未读人员-->
	{if $bool}
		<div class="ui-form">
			<div class="ui-form-item ui-form-item-order ui-border-b  ui-form-item-link">
				{if $unread_total ==0}
				<b>未读人员</b><span style="margin-left:20px">0人</span>
				
				{else}
					<a href="{$unread_url}"><b>未读人员</b><span style="margin-left:20px">{$unread_total}人</span></a>
				{/if}
			</div>
		</div>
	{/if}


<!--评论 回复-->
{if $news['is_comment'] == 1 && $news['is_publish'] == 1}

    <div class="ui-comment-box">
        <div class="ui-textarea-btn clearfix" style="margin-left:10px;border-bottom: 1px solid #ccc;padding-bottom: 5px;">
            <div class="ui-txt-muted" style="float: left;">  <span class="like-ziti" style="display:block;">评论 (<span id="comments_num">{$news['comment_num']}</span>)</span></div>
            <button class="ui-btn ui-btn-info" id="reply">评论</button>
        </div>
    </div>
	<ul class="ui-list ui-border-no" id="comments_list">
			{foreach $news['comments'] as $comment}
			<li class="ui-border-t ">
				<div class="dis-con-b clearfix">
				<div class="ui-avatar-s">
					<span style="background-image:url({$comment['avatar']}?80*80)"></span>
				</div>
				<div class="discuss-list-info">
					<h4>{$comment['m_username']}{if !empty($comment['p_username'])} 回复 {$comment['p_username']}{/if}</h4>
					<p class="discuss-time">{$comment['_created']}</p>
				</div>
				<div class="post_reply"  data-username="{$comment['m_username']}"><img src="/misc/images/discuss.png" alt=""/></div>
				</div>
				<div class="discuss-content">{$comment['content']}</div>
			</li>
			{foreachelse}
				<li class="none-discuss-box"><p class="none-discuss">暂无评论,赶紧来说两句吧!</p></li>
			{/foreach}
	</ul>
</div>

    <!--弹出框-->
    <div class="ui-dialog">
        <div class="ui-dialog-cnt">
                <div class="ui-dialog-bd">
                    <input type="hidden" id="ne_id" name="ne_id" value="{$news['ne_id']}">
                    <input type="hidden" id="p_id" name="p_id">
                    <input type="hidden" id="p_username" name="p_username">
                    <textarea placeholder="输入评论内容..." id="content" name="content" maxlength="140"></textarea>
                </div>
                <div class="ui-dialog-ft ui-btn-group">
                    <button type="button"   class="select" id="message_cancel">取消</button>
                    <button  class="select" id="send">确定</button>
                </div>
        </div>
    </div>
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "submit", "frozen"], function($, _, submit) {	
	//计算评论框剩余字符
	$('#content').bind('keyup', function () {
		var obj = $(this);
		var maxlength = obj.attr('maxlength');
		if (maxlength == 'undefined' || maxlength <= 0 || maxlength == null) {
			return;
		}
		var val = obj.val();
		//一个换行符，系统maxlength属性默认为两个字符，所以要再减去一个
		var re = new RegExp("\n", "g");
		var arr = val.match(re);
		var plus = 0;
		if (arr != null) {
			plus = arr.length;
		}
		var remainder = parseInt(maxlength) - parseInt(val.length) - plus;
		$('#remainder').html(remainder + '/' + maxlength);
	});

    function _show_form(e,tips,required) {
        $('#content').attr('placeholder',tips);
        $(".ui-dialog").dialog("show");
        $('#content').data('required', required);
        $('#content').val('');
        $('#content').focus();
    }

    //评论
    $('#reply').bind('click',function(){
        _show_form(this, '请填写评论内容',0);
    });

    //取消
    $('#message_cancel').bind('click',function(){
        $(".ui-dialog").dialog("hide");
    });

    $('#comments_list').on('click', '.post_reply', function(e){
        var p_username = $(this).attr('data-username');
        _show_reply(p_username, '请填写回复内容',0);
    });
    function _show_reply(p_username,tips,required) {
        //var url = '/frontend/thread/reply?handlekey=post&tid='+tid+"&pid="+pid+"&p_uid="+p_uid+"&p_username="+p_username;
        $('#content').attr('placeholder',tips);
        $(".ui-dialog").dialog("show");
        $('#content').data('required', required);
        $('#p_username').attr('value', p_username);
        $('#content').val('');
        $('#content').focus();
    }


    //发送评论
	$('#send').bind('click',function(){
		var ne_id = $('#ne_id').val();
        var p_username = $('#p_username').val();
		var content = $.trim($('#content').val());
		if (content == '') {
			$.tips({ content:'评论内容不能为空！', stayTime:2000, type:"warn"})
			return;
		}
		var el = null;
		$.ajax({
			'type': 'POST',
			'url': '/api/news/post/add',
            'cache':false,
			'data': {content: content, ne_id: ne_id,p_username:p_username},
			beforeSend: function () {
				el = $.loading({content: '正在发送...'})
			},
			success: function(data){
				el.loading('hide');
				if (data.errcode==0) {
                    var p_val = '';
                    if ((data.result.comment['p_username'] !== '')) {
                        p_val = " 回复 " + data.result.comment['p_username'];
                    }
                    // 输出回复数据
					var str = '<li class="ui-border-t">' +
							'<div class="dis-con-b clearfix"><div class="ui-avatar-s">' +
							'		<span style="background-image:url(' + data.result.comment['avatar'] + '?80*80)"></span>' +
							'	</div>' +
							'	<div class="discuss-list-info">' +
							'		<h4>' + data.result.comment['m_username'] + p_val + '</span></h4>' +
							'       <p class="discuss-time">'+ data.result.comment['_created'] +'</p></div>'+
							' <div class="post_reply" data-username="'+ data.result.comment['m_username'] +'"><img src="/misc/images/discuss.png" alt=""/></div></div>' +
							'	<div class="discuss-content">' + data.result.comment['content'] + '</div>' +
							'</li>';

					$('#comments_list').prepend(str);
					var comments_num = parseInt($('#comments_num').html());
					$('#comments_num').html(comments_num + 1);
					$('#content').val('');
                    $(".ui-dialog").dialog("hide");
                    $('#p_username').attr('value','');
					$('#remainder').html('140/140');
					$('.none-discuss-box').remove();
				}else{
					$.tips({content:data.errmsg,stayTime:2000,type:"warn"})
                    $(".ui-dialog").dialog("hide");
				}
			},
			error: function(){
				el.loading('hide');
			}
		});
		
	});
});
</script>
{/literal}
{/if}

{include file='mobile/footer.tpl'}