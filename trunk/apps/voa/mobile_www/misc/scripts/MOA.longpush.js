var LongPush = function() {
    var a = 800, b = "longpushBox", c = function(a) {
        clearTimeout(this._timeout), this._evts.onCloseBox(null), window.addEventListener("touchstart", this._evts.onCloseBox, !0);
        var c = $one("." + b, a);
        $show(c), $addCls(a, "tobeRemove");
    }, d = function(a) {
        if (null != a && (a.preventDefault(), a.stopPropagation(), window.removeEventListener("touchstart", this._evts.onCloseBox, !0), 
        new RegExp(b).test(a.target.className))) return void this._cfg.onBoxClick.call(a.target, a.target.parentNode);
        var c = $all("." + b, $one(this._cfg.ulCtx));
        $each(c, function(a) {
            $hide(a), $rmCls(a.parentNode, "tobeRemove");
        });
    }, e = function(a) {
        if (clearTimeout(this._timeout), a.currentTarget.removeEventListener("touchend", this._evts.te), 
        a.currentTarget.removeEventListener("touchcancel", this._evts.te), a.currentTarget.removeEventListener("touchmove", this._evts.te), 
        "touchmove" == a.type) {
            var c = document.createEvent("UIEvent");
            return c.initUIEvent("touchstart", !0, !0, window, 1), c.touches = a.touches, void $one(this._cfg.ulCtx).dispatchEvent(c);
        }
        new RegExp(b).test(a.currentTarget.className) ? (a.preventDefault(), a.stopPropagation()) : this._cfg.onLiClick.call(null, a.currentTarget);
    }, f = function(b) {
        var c = b.currentTarget;
        b.stopPropagation(), c.addEventListener("touchmove", this._evts.te), c.addEventListener("touchend", this._evts.te), 
        c.addEventListener("touchcancel", this._evts.te), this._timeout = MOA.utils.setTimeout(this._evts.onLongPush, a, c);
    }, g = function(a) {
        this._cfg = a, this._evts = {
            onCloseBox: MOA.event.delegate(d, this),
            onLongPush: MOA.event.delegate(c, this),
            ts: MOA.event.delegate(f, this),
            te: MOA.event.delegate(e, this)
        }, this._timeout = null;
    };
    return g.prototype = {
        parse: function(a) {
            var b = $all(this._cfg.liNodeName, $one(this._cfg.ulCtx)), c = this;
            $each(b, function(a) {
                /lpparsed/.test(a.className) || ($addCls(a, "lpparsed"), a.addEventListener("touchstart", c._evts.ts), 
                a.style[MOA.translate.vendor + "TouchCallout"] = "none", a.style[MOA.translate.vendor + "UserSelect"] = "none");
            }), "undefined" != typeof a && a.call(c);
        }
    }, g;
}();