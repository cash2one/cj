// Includes File Dependencies
requirejs(['router', "jquery"], function(router, $) {
	if (window.mobile) {
		$(document).on("mobileinit",
			// Set up the "mobileinit" handler before requiring jQuery Mobile's module
			function() {
				// Prevents all anchor click handling including the addition of active button state and alternate link bluring.
				$.mobile.linkBindingEnabled = false;

				// Disabling this will prevent jQuery Mobile from handling hash changes
				$.mobile.hashListeningEnabled = false;

				//$.mobile.autoInitializePage = false;
				//$.mobile.ajaxEnabled = false;
				//$.mobile.pushStateEnabled = false;
			}
		);
		$(function() {
			require(["jquery-mobile"], function() {
				// Initialize jqm page
				$.mobile.initializePage();
				// Instantiates a  Router
				// 路由初始化
				router.init();
			});
		});
	} else {
		router.init();
	}
});
