function HSliderChooser(a) {
    var b = MOA.translate.vendor, c = MOA.translate.open, d = MOA.translate.close, e = (MOA.event.delegate, 
    $one), f = $all, g = $each, h = $addCls, i = $rmCls, j = MOA.env.touchSupport, k = $data, l = e(a.containerContext), m = e(a.innerContext, l), n = a.nums, o = a.itemWidth, p = 1, q = (l.clientWidth, 
    {
        time: 0,
        left: 0,
        top: 0,
        x: 0,
        y: 0,
        lx: 0,
        ly: 0
    }), r = {
        left: 0
    }, s = null, t = function() {
        return new Date().getTime();
    }, u = function(a) {
        var b = a.touches[0], c = a.currentTarget.parentNode, d = c.parentNode.getClientRects()[0];
        return {
            x: b.pageX - d.left,
            y: b.pageY - d.top,
            px: b.pageX,
            py: b.pageY,
            cx: b.clientX,
            cy: b.clientY,
            sx: b.screenX,
            sy: b.screenY
        };
    }, v = function(a) {
        return parseFloat(/\((\-?[\.\d]+)(px)?/.exec(a.style[b + "Transform"])[1]);
    }, w = function(b) {
        p = b, g(f(a.itemContext, m), function(c, d) {
            i(c, a.currentStyleClass), b == d && h(c, a.currentStyleClass);
        });
    }, x = function(a, e) {
        var f = e ? -a * o : a, g = e ? .2472 : 0;
        m.style[b + "TransitionDuration"] = g + "s", m.style[b + "Transform"] = c + f + "px,0" + d;
    }, y = function(a) {
        j || a.preventDefault(), s = null;
        var b = u(a), c = {
            x: 1 * r.left,
            y: m.getClientRects()[0].top
        };
        q = {
            time: t(),
            left: c.x,
            top: c.y,
            x: parseInt(c.x - b.x),
            y: parseInt(c.y - b.y),
            lx: a.touches[0].clientX,
            ly: a.touches[0].clientY,
            point: b
        }, a.currentTarget.addEventListener("touchmove", z), a.currentTarget.addEventListener("touchend", A), 
        a.currentTarget.addEventListener("touchcancel", A);
    }, z = function(a) {
        j || a.preventDefault();
        var b, c, d = u(a), e = q.x + d.x, f = a.touches[0].pageX - q.point.px, g = a.touches[0].pageY - q.point.py;
        if ("y" !== s) {
            if ("x" === s) a.preventDefault(); else {
                if (b = Math.abs(f), c = Math.abs(g), 4 > b) return;
                if (c > .58 * b) return void (s = "y");
                a.preventDefault(), s = "x";
            }
            x(e);
            var h = a.currentTarget.parentNode;
            (void 0 === k(h, "touching") || 1 * k(h, "touching") != 1) && k(h, "touching", 1);
        }
    }, A = function(b) {
        j || b.preventDefault(), b.currentTarget.removeEventListener("touchmove", z), b.currentTarget.removeEventListener("touchend", A), 
        b.currentTarget.removeEventListener("touchcancel", A);
        var c = b.currentTarget.parentNode;
        k(c, "touching", 0);
        try {
            r.left = v(c);
        } catch (d) {}
        var e = p, f = {
            x: r.left,
            y: c.getClientRects()[0].top
        }, g = t() - q.time, h = f.x - q.left, i = Math.abs(h) < 5, l = g > 300;
        if (l || i) {
            if (Math.abs(h) > .5 * o) {
                var m = Math.abs(Math.round(h / o));
                0 > h ? e += m : e -= m;
            }
        } else 0 > h ? e++ : e--;
        1 > e && (e = 1), e >= n.length - 2 && (e = n.length - 2), x(e - 1, !0), r.left = v(c), 
        w(e), "callback" in a && a.callback && a.callback.call(null, p - 1);
    };
    n.unshift(-1), n.push(-1), g(n, function(b, c, d) {
        m.insertAdjacentHTML("beforeEnd", "<" + a.itemContext + ">" + b + "</" + a.itemContext + ">");
        var f = e(a.itemContext + ":last-of-type", m);
        return 0 == c || c == d.length - 1 ? void h(f, "sider") : (f.addEventListener("touchstart", y), 
        void (f = null));
    }), m.style.width = o * n.length + "px", "defaultIndex" in a ? (x(a.defaultIndex, !0), 
    r.left = v(m), a.defaultIndex += 1) : a.defaultIndex = 1, w(a.defaultIndex);
}