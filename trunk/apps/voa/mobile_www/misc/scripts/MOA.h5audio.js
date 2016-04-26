var MOA;

!function(a) {
    !function(b) {
        var c = a.env, d = a.event.fakeClick, e = function() {
            function a(a, b) {
                "undefined" == typeof b && (b = !0);
                var e = this;
                this.srcArr = a, this.autoplay = b, this.playing = !1, this.started = !1, this._currentTime = 0, 
                this._duration = 0, this._canplay = !1, this._iserror = !1, this._to = 0, this._errTryTimes = 5, 
                this._callbacks = {}, this._evts = {}, this._dom = new Audio(a), this._dom.preload = "auto", 
                this._dom.autobuffer = "auto", this._dom.loop = !1, this._registEvts(), b && (c.ios ? d(function() {
                    e.getDom().load();
                }) : (this.playing = !0, this.started = !0, this._dom.autoplay = !0));
            }
            return a.prototype.play = function() {
                var a = this;
                this._canplay ? this._play() : this._errTryTimes-- ? (this._to = window.setTimeout(function() {
                    a.play.call(a);
                }, 1e3), console.log("[MAudio] loading ", this.srcArr[0])) : (this._iserror = !0, 
                "error" in this._callbacks && this._callbacks.error.call(null));
            }, a.prototype.stop = function() {
                this._unregistEvts(), window.clearTimeout(this._to), this._currentTime = 0, this.pause(), 
                this._dom.parentNode && this._dom.parentNode.removeChild(this._dom), console.log("[MAudio] stop ", this.srcArr[0]);
            }, a.prototype.pause = function() {
                this._iserror || (this._currentTime = this._dom.currentTime, this._dom.pause(), 
                this.playing = !1, console.log("[MAudio] pause at ", this._currentTime, this.srcArr[0]));
            }, a.prototype.resume = function() {
                if (!this._iserror) {
                    try {
                        this._dom.currentTime = this._currentTime;
                    } catch (a) {}
                    this._dom.play(), this.playing = !0, console.log("[MAudio] resume at ", this._currentTime, this.srcArr[0]);
                }
            }, a.prototype.replay = function() {
                this.stop(), this._registEvts(), this.play();
            }, a.prototype.changeSong = function(a) {
                var b = this;
                this.started = !1, this.playing = !1, this._errTryTimes = 5, this._unregistEvts(), 
                this._iserror = !1, this._dom.src = a, this.srcArr = [ a ], this._registEvts(), 
                c.ios && d(function() {
                    b._dom.load();
                }), this._play(), console.log("[MAudio] song has been changed to: ", a);
            }, a.prototype.getDom = function() {
                return this._dom;
            }, a.prototype.registCallback = function(a, b) {
                this._callbacks[a] = b;
            }, a.prototype._listen = function(a, b, c) {
                "undefined" == typeof c && (c = !0), this._evts[a] = b, this._dom.addEventListener(a, b, c);
            }, a.prototype._registEvts = function() {
                var a = this;
                this._listen("error", function() {
                    a._iserror = !0, a.stop(), "error" in a._callbacks && a._callbacks.error.call(null);
                }), this._listen("loadstart", function() {
                    "loadstart" in a._callbacks && a._callbacks.loadstart.call(null);
                }, !1), this._listen("progress", function() {
                    try {
                        var b = a.getDom().buffered.end(0), c = a.getDom().duration;
                        if (isNaN(c) || isNaN(b)) return;
                        var d = b / c * 100;
                        d = Math.floor(d), "progress" in a._callbacks && a._callbacks.progress.call(null, d);
                    } catch (e) {}
                }, !1), this._listen("canplay", function() {
                    return c.ios ? (a.started || (a.playing = !0, a.started = !0, a._canplay = !0, a.autoplay && a._play()), 
                    void ("canplay" in a._callbacks && a._callbacks.canplay.call(null))) : ("canplay" in a._callbacks && a._callbacks.canplay.call(null), 
                    void (a._canplay = !0));
                }, !1), this._listen("ended", function() {
                    a.stop(), "ended" in a._callbacks && a._callbacks.ended.call(null);
                }, !1);
            }, a.prototype._unregistEvts = function() {
                for (var a in this._evts) try {
                    this._dom.removeEventListener(a, this._evts[a]), this._evts[a] = null;
                } catch (b) {}
            }, a.prototype._play = function() {
                this.started = !0, this._currentTime = 0, this.resume(), console.log("[MAudio] playing ", this.srcArr[0]);
            }, a.canPlayM4a = !!document.createElement("audio").canPlayType("audio/mpeg"), a;
        }();
        b.MAudio = e;
    }(a.media || (a.media = {}));
    a.media;
}(MOA || (MOA = {}));