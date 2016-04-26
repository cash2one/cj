function parseA2ZSearch(a) {
    var b = a.topHeight || 45, c = a.charHeight || 22, d = "mod_a2z_bigletter", e = $one(".mod_a2z_search"), f = a.searchFunc || function(a) {
        var b = null;
        return $each("body>abbr", function(c) {
            c.innerHTML.toUpperCase() === a && (b = c);
        }), b;
    }, g = function(a) {
        var c = a.getBoundingClientRect().top + window.scrollY;
        c -= b, document.body.scrollTop = c, window.scrollTo(0, c);
    }, h = a.lettersRange || null, i = function(a, b) {
        e.insertAdjacentHTML("beforeEnd", '<li class="' + a + '">' + b + "</li>");
    }, j = (window.innerHeight - c - b) / 26, k = function() {
        return null != h ? (e.style.top = 0, e.style.bottom = "auto", e.style.height = window.innerHeight + "px", 
        $data(e, "ffixTop", 0), void MOA.utils.fixStyleFixed(!0)) : (MOA.utils.pageToTop(), 
        void setTimeout(function() {
            window.innerHeight > 392 ? (e.style.height = window.innerHeight - c + "px", e.style.paddingTop = e.style.paddingBottom = "9px", 
            $each($all("li", e), function(a) {
                "topper" != a.className && (a.style.height = j + "px");
            })) : e.style.height = "392px", document.body.appendChild(e);
        }, 500));
    }, l = function(a) {
        if (b + 9 > a) return "topper";
        var c = parseInt((a - b - 9) / j);
        return null != h ? c < h.length ? h[c] : -1 : String.fromCharCode(65 + c);
    };
    i("topper", "&nbsp;");
    var m, n, o;
    if (null != h) {
        for (n = 0, o = h.length; o > n; n++) m = h[n], i(m, m);
        j = c;
    } else for (n = 0, o = 26; o > n; n++) m = String.fromCharCode(65 + n), i(m, m);
    k(), $each($all("li", e), function(a) {
        a.innerHTML && a.addEventListener("click", function(a) {
            var b = a.currentTarget.innerHTML, c = f(b);
            c && g(c);
        });
    });
    var p = function() {
        var a = $one("." + d);
        a && (a.parentNode.removeChild(a), a = null);
    }, q = function() {
        _disableSafariElastic(), window.addEventListener("touchmove", r), window.addEventListener("touchend", s), 
        window.addEventListener("touchcancel", s);
    }, r = function(a) {
        p();
        var b = parseInt(a.touches[0].pageY - window.scrollY), c = l(b);
        if (-1 != c) {
            var e = f(c);
            e && g(e), c.length > 1 || (big = document.createElement("div"), big.className = d, 
            big.style.top = window.scrollY + .5 * (window.innerHeight - 68) + "px", big.style.left = .5 * (window.innerWidth - 68) + "px", 
            big.innerHTML = c, document.body.appendChild(big), clearTimeout(window._cbto1), 
            window._cbto1 = setTimeout(function() {
                p();
            }, 2e3));
        }
    }, s = function() {
        p(), window.removeEventListener("touchmove", r), window.removeEventListener("touchend", s), 
        window.removeEventListener("touchcancel", s), _enableSafariElastic();
    };
    e.addEventListener("touchstart", q);
}