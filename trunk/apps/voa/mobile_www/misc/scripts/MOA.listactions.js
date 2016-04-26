window._parseModListActMenu = function() {
    var a = $one(".mod_list_actions_menuitem");
    a && 1 != $data(a, "initparsed") && ($data(a, "initparsed", 1), a.addEventListener("touchend", function(a) {
        a.stopPropagation(), a.preventDefault(), $each("ul", function(a) {
            var b = $all(".mod_list_actions_btns", a);
            setTimeout(function() {
                window.addEventListener("click", function() {
                    window.removeEventListener("click", arguments.callee), $each(b, function(a) {
                        $hide(a);
                    });
                });
            }, 500), $each(b, function(a) {
                $show(a), $each($all("a", a), function(a) {
                    $rmCls(a, "confirm");
                });
                var b = a.clientHeight, c = a.parentNode.clientHeight;
                a.style.paddingTop = parseInt(.5 * (c - b)) + "px";
            });
        });
    }), $each(".mod_list_actions_btns>a", function(a) {
        $data(a, "href", a.href), a.href = "javascript:void(0)", a.addEventListener("click", function(a) {
            var b = a.currentTarget;
            $hasCls(b, "confirm") || (a.stopPropagation(), $each($all("a", b.parentNode), function(a) {
                $rmCls(a, "confirm"), a.href = "javascript:void(0)";
            }), $addCls(b, "confirm"), setTimeout(function() {
                b.href = $data(b, "href");
            }, 200));
        });
    }));
}, $onload(function() {
    setTimeout(window._parseModListActMenu, 2e3), setTimeout(window._parseModListActMenu, 4e3), 
    setTimeout(window._parseModListActMenu, 6e3), window._parseModListActMenu;
});