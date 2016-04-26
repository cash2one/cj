define(["data/talk", "utils/render", "text!templates/chat.html","text!templates/chat_data.html", 'jquery', "iscrollview"
        , "css!styles/common.css","css!styles/chat.css", "css!styles/service.css"], function(talk, render, tpl, son_tpl, $){
	
	
    function view() {
    	
    }
    
    //变量初始化
    var username = '';		//客户信息初始化
    var max_id = 0;			//最后消息id
    var fresh_time = 3;		//刷新时间
    var minus_time = 600;	//多少秒未聊天后,显示时间信息
    var tv_id = 0;			//客户id
    var goods_id = 0;		//产品id
    //var goods_id = 0;		//相关产品id
    var FLAG_INIT = 1;		//初始化聊天记录
    var FLAG_LATEST = 2;	//读最新数据
    var FLAG_RECORD = 3;	//读旧记录
    var loading = 0;		//是否加载中状态
    
    
    view.prototype.render = function(args) {
    	var self = this;
    	goods_id = args.goods_id;
    	tv_id = args.tv_id;
    	
    	
    	//初始化页面
    	self.render = new render();
	    self.render.template = tpl;
	    self.page = self.render.apply();
    	self.refresh(1);
    	 
    	//获取产品名称和客户姓名
    	args.sale_id = userinfo.m_uid;
    	talk.init(args, function (ret){
    		username = ret.tv_name;
    		$('h1').html('访客: ' + username);
			$('title, .good-title').html(ret.goods_name);
    	});
    	//清除定时器后再开启,以免重复
    	if(window.timer1) clearInterval(timer1);
    	if(window.timer2) clearInterval(timer2);
    	timer2 = setInterval(function (){
    		self.refresh();
    	}, fresh_time * 1000);
    	
    	
    };
    
    view.prototype.refresh = function (is_first){
    	if(loading > 0) return;
    	loading = 1;
    	var self = this;
    	var params = {goods_id: goods_id, uid:userinfo.m_uid,tv_id:tv_id, tw_id: max_id, flag: FLAG_LATEST};
    	
    	if(is_first) {
    		params.flag = FLAG_INIT;
    	}
    	// 数据业务处理处
        talk.get_chat(params, function (ret) {
        	var html = "";
        	if(!ret.data) ret.data = [];
        	
        	$.each(ret.data, function (i, item){
        		if(!item) return;
        		if($('.chat-time[time='+item.create+']').length) return;
        		if(is_sales) {
					type = item.tw_type == 1 ? 'you' : 'me';
				}else{
					type = item.tw_type == 1 ? 'you' : 'me';
				}
        		html += self.render.parse_template(son_tpl, {item: item, type: type});
        		if(item.tw_id * 1 > max_id * 1) {
        			max_id = item.tw_id * 1;
        		}
        	});
        	//渲染页面
            self.page.find('#listDiv').append(html);
            
            
            //翻到最底部
            if(ret.data.length > 0) {
            	//$.mobile.silentScroll(99999);
            	self.page.find('#scrollDiv').animate({scrollTop:30000},150);
            }
            
            //判断是否显示时间
            var pre_time = 0;
            $('.chat-time').each(function (i, e){
            	var time = $(e).attr('time');
            	if(time - pre_time > minus_time) {
            		$(e).show();
            	}
            	pre_time = time;
            });
			
            //判断是否有新访客
            if(ret.newguest > 0) $('span.num').text(ret.newguest).show();
            
			if(max_id > 0) {
        		$('.empty').remove();
        	}
        	$('#msg').focus();
        	if(is_first) {
        		self.event(self.page);
        		if(ret.data.length > 0) {
        			setTimeout("$.mobile.silentScroll(99999)", 300);
        		}
        	}
        	loading = 0;
        });
    };
    
    // 监听事件   
    view.prototype.event = function (el) {
    	var self = this;
    	
    	el.find('#send').click(function (){
    		var msg = $('#msg').val();
    		if(!msg) return;
    		talk.say({goods_id: goods_id, uid: userinfo.m_uid,tv_id: tv_id, message: msg, tw_id: max_id, flag: FLAG_LATEST}, function (ret) {
				$('#msg').html('').val('');
				self.refresh();
    		});
    	});
    };
    return view;
});
