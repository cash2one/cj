var MTouchLoopSlider = function() {
    var a = MOA.translate.vendor, b = MOA.translate.open, c = MOA.translate.close, d = MOA.event.delegate, e = function(d, e, f) {
        var g = f ? .2472 : 0;
        d.style[a + "TransitionDuration"] = g + "s", d.style[a + "Transform"] = b + e + "px,0" + c;
    }, f = function() {
        return new Date().getTime();
    }, g = function(a) {
        var b = {
            outerDom: ".mod_touch_slider",
            innerDom: ".sld_bar",
            itemDom: ".sld_page",
            autoplay: !1,
            autotimeout: 5e3
        };
        for (var c in b) c in a || (a[c] = b[c]);
        if (this._config = a, this._outer = $one(a.outerDom), this._inner = $one(a.innerDom, this._outer), 
        this._items = $all(a.itemDom, this._inner), this._autoItv = null, !(this._items.length < 2)) {
            if (2 == this._items.length) {
                var e = this._items[0], f = this._items[1], g = e.cloneNode(!0), h = f.cloneNode(!0);
                this._fakeForJustTwo = !0, $data(g, "fakeForJustTow", 1), $data(h, "fakeForJustTow", 1), 
                this._inner.appendChild(g), this._inner.appendChild(h), this._items = $all(a.itemDom, this._inner);
            }
            this.setWidth(this._outer.clientWidth), this._events = {
                ts: d(this._ets, this),
                tm: d(this._etm, this),
                te: d(this._ete, this)
            };
            var i = "defaultIndex" in a && "undefined" != typeof a.defaultIndex ? parseInt(a.defaultIndex) : 0;
            this._default = i, this._range = this._getRange(i), this._render(), this._inner.addEventListener("touchstart", this._events.ts);
        }
    };
    return g.prototype = {
        setWidth: function(a) {
            this._w = a;
            var b = this._w;
            this._outer.style.width = b + "px", this._inner.style.width = 3 * b + "px", $each(this._items, function(a) {
                a.style.width = b + "px";
            });
        },
        fixHeight: function() {
            var a = this._items[this._range[1]].clientHeight + "px", b = this._inner.parentNode;
            this._outer.style.height = this._inner.style.height = b.style.height = a, b = null;
        },
        setCurrent: function(a) {
            isNaN(a) && (a = 0), this._range = this._getRange(a), this._render();
        },
        _getRange: function(a) {
            isNaN(a) && (a = 0);
            var b = [ a ];
            return b.unshift(0 == a ? this._items.length - 1 : a - 1), b.push(a == this._items.length - 1 ? 0 : a + 1), 
            b;
        },
        _render: function() {
            this._disableAuto(), this._inner.removeEventListener("touchstart", this._events.ts), 
            this._inner.removeEventListener("touchmove", this._events.tm), this._inner.removeEventListener("touchend", this._events.te), 
            this._inner.removeEventListener("touchcancel", this._events.te);
            var a = this;
            this._inner.innerHTML = "", $each(this._range, function(b) {
                a._inner.appendChild(a._items[b]);
            }), e(this._inner, -this._w, !1), this.fixHeight(), this._inner.addEventListener("touchstart", this._events.ts), 
            this._enableAuto();
        },
        _enableAuto: function() {
            if (this._config.autoplay && null === this._autoItv) {
                var a = this;
                this._autoItv = window.setTimeout(function() {
                    e(a._inner, -2 * a._w, !0), a._inner.addEventListener("webkitTransitionEnd", function(b) {
                        b.currentTarget.removeEventListener("webkitTransitionEnd", arguments.callee), a._ontransend(2);
                    });
                }, a._config.autotimeout);
            }
        },
        _disableAuto: function() {
            this._config.autoplay && (window.clearTimeout(this._autoItv), this._autoItv = null);
        },
        _ets: function(a) {
            this._disableAuto(), this._directionLocked = !1;
            var b = this._outer.getBoundingClientRect().left, c = this._inner.getBoundingClientRect().left;
            this.dinfo_start = {
                time: f(),
                localX: a.touches[0].clientX - b,
                stageX: a.touches[0].clientX,
                stageY: a.touches[0].clientY,
                innerLeft: c - b
            }, this._inner.addEventListener("touchmove", this._events.tm), this._inner.addEventListener("touchend", this._events.te), 
            this._inner.addEventListener("touchcancel", this._events.te);
        },
        _etm: function(a) {
            var b, c, d = a.touches[0].pageX - this.dinfo_start.stageX, f = a.touches[0].pageY - this.dinfo_start.stageY;
            if ("y" !== this._directionLocked) {
                if ("x" === this._directionLocked) a.preventDefault(); else {
                    if (b = Math.abs(d), c = Math.abs(f), 4 > b) return;
                    if (c > .58 * b) return void (this._directionLocked = "y");
                    a.preventDefault(), this._directionLocked = "x";
                }
                var g = a.touches[0].clientX - this.dinfo_start.stageX + this.dinfo_start.innerLeft;
                e(this._inner, g, !1);
            }
        },
        _ete: function(a) {
            a.preventDefault(), this._inner.removeEventListener("touchmove", this._events.tm), 
            this._inner.removeEventListener("touchend", this._events.te), this._inner.removeEventListener("touchcancel", this._events.te);
            var b = this._outer.getBoundingClientRect().left, c = this._inner.getBoundingClientRect().left;
            this.dinfo_end = {
                time: f(),
                innerLeft: c - b
            };
            var d = this, g = this.dinfo_end.innerLeft - this.dinfo_start.innerLeft, h = Math.abs(g) < 5, i = this.dinfo_end.time - this.dinfo_start.time, j = i > 300, k = 0 > g, l = -this._w, m = null;
            j ? Math.abs(g) > .5 * this._w && (l = k ? -2 * this._w : 0, m = k ? 2 : 0) : h || (l = k ? -2 * this._w : 0, 
            m = k ? 2 : 0), a.currentTarget.removeEventListener("touchstart", this._events.ts), 
            e(this._inner, l, !0), null != m ? this._inner.addEventListener("webkitTransitionEnd", function(a) {
                a.currentTarget.removeEventListener("webkitTransitionEnd", arguments.callee), d._ontransend(m), 
                a.currentTarget.addEventListener("touchstart", d._events.ts);
            }) : a.currentTarget.addEventListener("touchstart", d._events.ts);
        },
        _ontransend: function(a) {
            this._range = this._getRange(this._range[a]), this._fakeForJustTwo && (1 == this._range[0] && (this._range = [ 3, 0, 1 ]), 
            2 == this._range[0] && (this._range = [ 0, 1, 2 ])), this._render(), "callback" in this._config && "function" == typeof this._config.callback && this._config.callback.call(this, this._outer, this._items[this._range[1]], this._range[1]);
        }
    }, g;
}();