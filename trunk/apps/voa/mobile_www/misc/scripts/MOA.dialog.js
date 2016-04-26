var MDialog = function() {
    var a = MOA.query.one, b = MOA.query.all, c = (MOA.utils.forEach, MOA.utils.setTimeout), d = MOA.data, e = "javascript:void(0)", f = function(a) {
        return "undefined" == typeof a;
    }, g = function() {
        var c = '<div class="mModal"><a href="' + e + '"></a></div>';
        a("body").insertAdjacentHTML("beforeEnd", c), c = null;
        var d = a(".mModal:last-of-type");
        return b(".mModal").length > 1 && (d.style.opacity = .01), a("a", d).style.height = window.innerHeight + "px", 
        d.style.zIndex = window._dlgBaseDepth++, d;
    }, h = function(a, b) {
        var c = a && /mModal/.test(a.currentTarget.className);
        if (!c) return !0;
        var e = parseInt(d(b, "closeByModal"));
        return e = !isNaN(e) && 1 === e, c && e ? !0 : !1;
    }, i = function() {
        f(window._dlgBaseDepth) && (window._dlgBaseDepth = 900);
    }, j = function(b) {
        f(b) && (b = !0), a("body").style.overflow = b ? "hidden" : "visible";
    }, k = function(b, c, k, l, m, n, o, p, r, s, t, u, v) {
        f(c) && (c = null), f(k) && (k = null), f(m) && (m = null), f(n) && (n = null), 
        f(o) && (o = null), f(p) && (p = null), f(r) && (r = null), f(s) && (s = null), 
        f(t) && (t = !0), f(u) && (u = !0), i();
        var w = window.innerWidth, x = window.innerHeight, y = null, z = null;
        u && (z = g()), y = '<div class="mDialog"><figure></figure><h1></h1><h2></h2><h3></h3><footer>	<a class="two"></a>	<a class="two"></a>	<a class="one"></a></footer><a class="x">X</a></div>', 
        a("body").insertAdjacentHTML("beforeEnd", y), y = null;
        var A = a("div.mDialog:last-of-type", a("body")), B = a("figure", A), C = a("footer a.one", A), D = a("footer a.two:nth-of-type(1)", A), E = a("footer a.two:nth-of-type(2)", A), F = a("a.x", A), G = 0, H = [], I = function(a, b, c) {
            a.addEventListener(b, c), H.push({
                o: a,
                e: b,
                f: c
            });
        }, J = function(b, c) {
            var d = a(b, A);
            return null != c ? d.innerHTML = c : d.parentNode.removeChild(d), d;
        }, K = function(a) {
            if (h(a, A)) {
                for ("function" == typeof v && v(); H.length; ) {
                    var b = H.shift();
                    b.o.removeEventListener(b.e, b.f);
                }
                A.parentNode.removeChild(A), window._dlgBaseDepth--, null != z && (z.parentNode.removeChild(z), 
                window._dlgBaseDepth--, j(!1));
            }
        }, L = J("h1", b);
        return L.clientHeight > 51 && (L.style.textAlign = "left"), J("h2", c), J("h3", k), 
        null == s ? A.removeChild(B) : $addCls(B, s), B = null, null == o ? (D.parentNode.removeChild(D), 
        E.parentNode.removeChild(E), D = null, E = null, C.innerHTML = l, C.href = e, null != n && $addCls(C, n), 
        null != m && I(C, "click", m), I(C, "click", K)) : (C.parentNode.removeChild(C), 
        C = null, D.innerHTML = l, D.href = e, null != n && $addCls(D, n), null != m && I(D, "click", m), 
        I(D, "click", K), E.innerHTML = o, E.href = e, null != r && $addCls(E, r), null != p && I(E, "click", p), 
        I(E, "click", K)), t ? (F.href = e, I(F, "click", K)) : (F.parentNode.removeChild(F), 
        F = null), null != z && I(z, "click", K), A.style.zIndex = window._dlgBaseDepth++, 
        A.style.left = .5 * (w - A.clientWidth) + "px", G = parseInt(.45 * (x - A.clientHeight)), 
        A.style.top = G + "px", d(A, "ffixTop", G), u && j(), q.close = K, A;
    }, l = function(a, b, c, d, e, f, g, h, i) {
        return k(a, b, c, d, e, f, null, null, null, g, h, i);
    }, m = function(b, e, g) {
        f(e) && (e = null), f(g) && (g = 3e3);
        var h = '<div class="mNotice">	<span></span></div>';
        a("body").insertAdjacentHTML("beforeEnd", h), i();
        var j = a("div.mNotice:last-of-type", a("body")), k = a("span", j), l = window.innerWidth, m = window.innerHeight, n = 0;
        return k.innerHTML = b, null != e && $addCls(k, e), j.style.zIndex = window._dlgBaseDepth++, 
        j.style.left = .5 * (l - j.clientWidth) + "px", n = parseInt(.45 * (m - j.clientHeight)), 
        j.style.top = n + "px", d(j, "ffixTop", n), c(function() {
            j.parentNode.removeChild(j), window._dlgBaseDepth--;
        }, g), j;
    }, n = function(b, c, k, l, m, n) {
        f(c) && (c = 295), f(k) && (k = !0), f(l) && (l = !0), f(m) && (m = null), f(n) && (n = null), 
        i();
        var o = window.innerWidth, p = window.innerHeight, r = null, s = null;
        l && (s = g()), r = '<div class="mImgPopup"><figure></figure><a class="x">X</a></div>', 
        a("body").insertAdjacentHTML("beforeEnd", r);
        var t = a("div.mImgPopup:last-of-type", a("body")), u = a("figure", t), v = (a("span", t), 
        a("a.x", t)), o = window.innerWidth, p = window.innerHeight, w = 0, x = [], y = function(a, b, c) {
            a.addEventListener(b, c), x.push({
                o: a,
                e: b,
                f: c
            });
        }, z = function(a) {
            if (h(a, t)) {
                for (null != n && n.call(null); x.length; ) {
                    var b = x.shift();
                    b.o.removeEventListener(b.e, b.f);
                }
                t.parentNode.removeChild(t), window._dlgBaseDepth--, null != s && (s.parentNode.removeChild(s), 
                window._dlgBaseDepth--, j(!1));
            }
        }, A = function(a) {
            var b = a.currentTarget, d = b.width, e = b.height, f = 1;
            u.appendChild(b), d > c && (f = d / c), u.style.height = t.style.height = b.style.height = parseInt(e / f) + "px", 
            u.style.width = t.style.width = b.style.width = parseInt(d / f) + "px", B(), null != m && m.call(null, b);
        }, B = function() {
            t.style.zIndex = window._dlgBaseDepth++, t.style.left = .5 * (o - t.clientWidth) + "px", 
            w = .5 * (p - t.clientHeight), t.style.top = w + "px", d(t, "ffixTop", w), l && j();
        };
        B(), k ? (v.href = e, y(v, "click", z)) : (v.parentNode.removeChild(v), v = null), 
        null != s && y(s, "click", z);
        var C = new Image();
        return y(C, "load", A), C.src = b, q.close = z, t;
    }, o = function(b, c) {
        if (!a("#mLoading")) {
            f(b) && (b = ""), f(c) && (c = !1), i();
            var d = window.innerWidth, e = window.innerHeight, h = null, j = null;
            c && (j = g(), j.id = "mLoadingModal"), h = '<div id="mLoading"><div class="lbk"></div><div class="lcont">' + b + "</div></div>", 
            a("body").insertAdjacentHTML("beforeEnd", h);
            var k = a("#mLoading");
            return k.style.top = a("body").scrollTop + .5 * (e - k.clientHeight) + "px", k.style.left = .5 * (d - k.clientWidth) + "px", 
            k;
        }
    }, p = function(b, c, k, l) {
        f(b) && (b = null), f(c) && (c = !0), f(k) && (k = null), f(l) && (l = !0), i();
        var m = window.innerWidth, n = window.innerHeight, o = null, p = null;
        l && (p = g()), o = '<div class="mDialog freeSet">' + b + '<a class="x">X</a></div>', 
        a("body").insertAdjacentHTML("beforeEnd", o), o = null;
        var r = a("div.mDialog:last-of-type", a("body")), s = a("a.x", r), t = 0, u = [], v = function(a, b, c) {
            a.addEventListener(b, c), u.push({
                o: a,
                e: b,
                f: c
            });
        }, w = function(a) {
            if (h(a, r)) {
                for (null != k && k(); u.length; ) {
                    var b = u.shift();
                    b.o.removeEventListener(b.e, b.f);
                }
                r.parentNode.removeChild(r), window._dlgBaseDepth--, null != p && (p.parentNode.removeChild(p), 
                window._dlgBaseDepth--, j(!1));
            }
        };
        return c ? (s.href = e, v(s, "click", w)) : (s.parentNode.removeChild(s), s = null), 
        null != p && v(p, "click", w), r.style.zIndex = window._dlgBaseDepth++, r.style.left = .5 * (m - r.clientWidth) + "px", 
        t = parseInt(.45 * (n - r.clientHeight)), r.style.top = t + "px", d(r, "ffixTop", t), 
        l && j(), q.close = w, r;
    }, q = {
        ICON_TYPE_SUCC: "succ",
        ICON_TYPE_WARN: "warn",
        ICON_TYPE_FAIL: "fail",
        BUTTON_STYLE_ON: "on",
        BUTTON_STYLE_OFF: "off",
        confirm: k,
        alert: l,
        notice: m,
        popupImage: n,
        showLoading: o,
        popupCustom: p,
        close: null
    };
    return q;
}(), MLoading = {
    show: MDialog.showLoading,
    hide: function() {
        var a = MOA.query.one("#mLoading");
        a && a.parentNode.removeChild(a);
        var b = MOA.query.one("#mLoadingModal");
        b && b.parentNode.removeChild(b);
    }
};