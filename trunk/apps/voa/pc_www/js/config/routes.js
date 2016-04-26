define({ "id": "routes", list: [
		/**
		 * 登录
		 */
		//{url: '/login', name: 'login'},
		/**
		 * 退出登录 
		 */
		{url: '/logout', name: 'logout'},
		/**
		 * 初始化
		 */
		{url: '/init', name: 'init'},
		/**
		 * 左侧菜单
		 */
		{url: '/leftmenu', name: 'leftMenu'},
		/**
		 * 更新左侧菜单数据
		 * @param attr 属性名 可能会有 aaa.bbb 的嵌套格式
		 * @param value
		 * @example /leftmenu/menu.企业通讯.list.公告.unread/0
		 */
		{url: '/leftmenu/:attr/:value', name: 'updateLeftLenu'},
		/**
		 * 通讯录默认列表&搜索
		 * @param page 页数 从0开始 始终存在
		 * @param query 查询关键字 为空则表示全部
		 */
		{url: '/addressbook/search/:page(/)(:query)', name: 'getAddressbookList'},
		/**
		 * 通讯录某人详细资料
		 * @param id
		 */
		{url: '/addressbook/:id', name: 'getAddressbookById'},
		/**
		 * 公告默认列表&搜索（业务逻辑暂时没有搜索）
		 * @param page 页数 从0开始 始终存在
		 * @param query 查询关键字 为空则表示全部
		 */
		{url: '/announcement/search/:page(/)(:query)', name: 'getAnnouncementList'},
		/**
		 * 公告详情
		 * @param id
		 */
		{url: '/announcement/:id', name: 'getAnnouncementById'},
		/**
		 * 取得签到相关功能的菜单
		 */
		{url: '/checkin/menu', name: 'getCheckInMenu' },
		/**
		 * 取得构建“每日签到”界面的数据
		 */
		{url: '/checkin/daily', name: 'getCheckInDaily'},
		/**
		 * 取得构建“考勤查询”界面的数据
		 * @param year
		 * @param month
		 */
		{url: '/checkin/calendar(/:year/:month)', name: 'getCheckInCalendar'},
		/**
		 * 取得构建“考勤申诉”界面的初始数据
		 */
		{url: '/checkin/complaint/:year/:month', name: 'getCheckInComplaint'},
		/**
		 * 处理“考勤申诉”的提交结果
		 */
		{url: '/checkin/complaint', name: 'putCheckInComplaint'},
		/**
		 * 取得设置相关功能的菜单
		 */
		{url: '/settings/menu', name: 'getSettingsMenu'},
		/**
		 * 取得设置中的个人资料
		 * @param id
		 */
		{url: '/settings/profile', name: 'getSettingsProfile'},
		/**
		 * 取得设置密码的初始界面数据
		 */
		{url: '/settings/password', name: 'settingsPassword'},


	]});
