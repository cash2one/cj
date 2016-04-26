var console = window.console || {
    log: function() {}
};

!function() {
    var a = "abbr,article,aside,audio,canvas,datalist,details,dialog,eventsource,figure,figcaption,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,small,time,video", b = a.split(",");
    try {
        var c = document.styleSheets[0], d = c.cssRules || c.rules, e = d.length, f = "display:block";
        if (c.insertRule) c.insertRule(a + "{" + f + "}", e); else if (c.addRule) for (var g = 0; g < b.length; g++) c.addRule(b[g], f, e);
    } catch (h) {}
    try {
        for (var i = 0; i < b.length; i++) document.createElement(b[i]);
    } catch (h) {}
}();