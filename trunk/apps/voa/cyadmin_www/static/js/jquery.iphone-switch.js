jQuery.fn.iphoneSwitch = function (options) {
    var settings = {
    	start_state: '',
        mouse_over: 'pointer',
        mouse_out: 'default',
        switch_on_container_path: 'iphone_switch_container_on.png',
        switch_off_container_path: 'iphone_switch_container_off.png',
        switch_path: 'iphone_switch.png',
        switch_height: 27,
        switch_width: 94,
        switched_on_callback: '',
        switched_off_callback: '',
        disable: '',
    };
    if (options) {
        jQuery.extend(settings, options);
    }
    return this.each(function () {
    	if (typeof settings.start_state == 'function') {
    		var state = settings.start_state(this);
    	} else {
    	    var state = settings.start_state == 'on' ? settings.start_state : 'off';
    	}
        var container;
        var image;
        container = jQuery('<div class="iphone_switch_container" style="height:' + settings.switch_height + 'px; width:' + settings.switch_width + 'px; position: relative; overflow: hidden"></div>');
        image = jQuery('<img class="iphone_switch" style="height:' + settings.switch_height + 'px; width:' + settings.switch_width + 'px; background-image:url(' + settings.switch_path + '); background-repeat:none; background-position:' + (state == 'on' ? 0 : -53) + 'px" src="' + (state == 'on' ? settings.switch_on_container_path : settings.switch_off_container_path) + '" />');
        jQuery(this).html(jQuery(container).html(jQuery(image)));
        jQuery(this).mouseover(function () {
            jQuery(this).css("cursor", settings.mouse_over);
        });
        jQuery(this).mouseout(function () {
            jQuery(this).css("background", settings.mouse_out);
        });
        jQuery(this).click(function () {
        	if (typeof settings.disable == 'function') {
        		if (settings.disable(this) == true) {
        			return false;
        		}
        	}
            if (state == 'on') {
                jQuery(this).find('.iphone_switch').animate({
                    backgroundPosition: -53
                }, "slow", function () {
                    jQuery(this).attr('src', settings.switch_off_container_path);
                    settings.switched_off_callback(this);
                });
                state = 'off';
            } else {
                jQuery(this).find('.iphone_switch').animate({
                    backgroundPosition: 0
                }, "slow", function () {
                	settings.switched_on_callback(this);
                });
                jQuery(this).find('.iphone_switch').attr('src', settings.switch_on_container_path);
                state = 'on';
            }
        });
    });
};