var MOA;

!function(MOA) {
    !function(_file) {
        var _getBoundary = function() {
            return "------html5mupboundary" + new Date().getTime();
        }, _fixChromeSendBinary = function() {
            "undefined" == typeof XMLHttpRequest.prototype.sendAsBinary && (XMLHttpRequest.prototype.sendAsBinary = function(a) {
                function b(a) {
                    return 255 & a.charCodeAt(0);
                }
                var c = Array.prototype.map.call(a, b), d = new Uint8Array(c);
                this.send(d.buffer);
            });
        }, _getXMLHttpRequest = function() {
            return new XMLHttpRequest();
        }, _getJSON = function(a) {
            return JSON.parse(a);
        }, _getUploadBinary = function(a, b, c) {
            var d = "--", e = "\r\n", f = "";
            return f += d, f += a, f += e, f += 'Content-Disposition: form-data; name="Filedata"', 
            c && (f += '; filename="' + escape(c) + '"'), f += e, f += "Content-Type: application/octet-stream", 
            f += e, f += e, f += b, f += e, f += d, f += a, f += e, f += d, f += a, f += d, 
            f += e;
        }, _isValidExtType = function(a, b) {
            var c = null, b = null;
            var ext = a.split('.');
            try {
                
                if (ext.length < 2) {
                    return true;
                }
                return c = a.match(/\.([^\.]*)$/i)[1].toLowerCase(), b = b ? b.toLowerCase() : "*.jpg;*.jpeg;*.gif;*.png", 
                new RegExp("." + c + ";?", "i").test(b);
            } catch (d) {}
            return !1;
        }, _makeCvs = function(a, b) {
            var c = new Image();
            c.onload = function() {
                var a = document.createElement("canvas");
                a.width = c.naturalWidth, a.height = c.naturalHeight;
                var d = a.getContext("2d");
                d.drawImage(c, 0, 0), b.call(null, a, c);
            }, c.src = a instanceof window.File ? _file.ImageCompresser.getFileObjectURL(a) : a;
        }, _doCommonUp = function(a, b) {
            var c = _getBoundary(), d = new FileReader();
            b.setRequestHeader("content-type", "multipart/form-data; boundary=" + c), "undefined" != typeof FileReader.prototype.readAsBinaryString ? (d.onload = function() {
                b.sendAsBinary(_getUploadBinary(c, d.result, a.name));
            }, d.readAsBinaryString(a)) : (d.onload = function() {
                for (var e = d.result, f = new Uint8Array(e), g = "", h = 0; h < f.length; h++) g += String.fromCharCode(f[h]);
                b.sendAsBinary(_getUploadBinary(c, g, a.name));
            }, d.readAsArrayBuffer(a));
        }, _doBase64Up = function(a, b, c, d) {
            if (MOA.env.ios && MOA.env.version > 7) {
                var e = document.createElement("img");
                e.style.opacity = "0", document.body.appendChild(e), e.onload = function() {
                    var f = e.naturalWidth, g = e.naturalHeight, h = _file.ImageCompresser.getIosImageRatio(e, f, g), i = document.createElement("canvas");
                    if (1 > h) {
                        var i = document.createElement("canvas"), j = (i.getContext("2d"), new Image());
                        j.onload = function() {
                            var c = j.src, d = new FormData();
                            d.append("base64Data", c), d.append("fileName", escape(a.name)), b.send(d);
                        }, new MegaPixImage(e).render(j, {
                            width: g,
                            height: f,
                            maxWidth: 1500,
                            maxHeight: 1500,
                            quality: c,
                            orientation: 6
                        });
                    } else _makeCvs(a, function(e, f) {
                        d._callback("progress", a, .5);
                        var g = {
                            quality: c
                        }, h = (a.type || "image/jpeg", _file.ImageCompresser.getImageBase64(f, g)), i = new FormData();
                        i.append("base64Data", h), i.append("fileName", escape(a.name)), b.send(i);
                    });
                    document.body.removeChild(e);
                }, e.src = _file.ImageCompresser.getFileObjectURL(a);
            } else _makeCvs(a, function(e, f) {
                d._callback("progress", a, .5);
                var g = {
                    quality: c
                }, h = (a.type || "image/jpeg", _file.ImageCompresser.getImageBase64(f, g)), i = new FormData();
                i.append("base64Data", h), i.append("fileName", escape(a.name)), b.send(i);
            });
        }, _isWifi = !1;
        MOA.env.weixin && document.addEventListener("WeixinJSBridgeReady", function() {
            WeixinJSBridge.invoke("getNetworkType", {}, function(a) {
                "network_type:wifi" === a.err_msg && (_isWifi = !0);
            });
        });
        var HTML5MUP = function() {
            function HTML5MUP(a) {
                this.setting = a, this._dom = null, this._container = null, this._submitBtn = null, 
                this._url = null, this._uploadingFlag = -1, this._serverRtnCache = [], this._source = [], 
                "undefined" == typeof a.autostart && (this.setting.autostart = !1), "undefined" == typeof a.multiple && (this.setting.multiple = !0), 
                this._container = document.getElementById(a.container_id), this._submitBtn = "submit_id" in a ? document.getElementById(a.submit_id) : null, 
                this._url = a.url, _fixChromeSendBinary(), this._createInput();
            }
            return HTML5MUP.prototype.upload = function() {
                this._uploadingFlag = 0, this._serverRtnCache = [], this._submitBtn && (this._submitBtn.disabled = !0), 
                this._source.length && this._queueUpload(), this._callback("uploadStart", this._source);
            }, HTML5MUP.prototype.cancelItem = function(a) {
                -1 == this._uploadingFlag && (this._source.splice(a, 1), this._callback("listChange", this._source));
            }, HTML5MUP.prototype.getDom = function() {
                return this._dom;
            }, HTML5MUP.prototype.enable = function(a) {
                "undefined" == typeof a && (a = !0), this._dom.disabled = !a, a || this._dom.removeAttribute("disabled");
            }, HTML5MUP.prototype.reactive = function() {
                this._uploadingFlag = -1, this._serverRtnCache = [], this._source = [];
            }, HTML5MUP.prototype._createInput = function() {
                var a = this, b = document.createElement("input");
                b.type = "file", b.name = "upfiles", b.id = "upfiles", this.setting.multiple && b.setAttribute("multiple", "multiple"), 
                this._dom = b, b.multiple && (b.name = "upfiles[]"), this._container.appendChild(this._dom), 
                this._dom.onchange = function() {
                    a._onSelect();
                }, this._submitBtn && (this._submitBtn.onclick = function() {
                    a.enable(), a.upload();
                }, this._submitBtn.disabled = !0);
            }, HTML5MUP.prototype._onSelect = function() {
                this._addFiles(this._dom.files);
            }, HTML5MUP.prototype._addFiles = function(a) {
                this._submitBtn && (this._submitBtn.disabled = !1, this._submitBtn.removeAttribute("disabled"));
                var b = [], c = [];
                if (a && a.length) for (var d = 0; d < a.length; d++) b.push(a[d]);
                if (this._source.length) {
                    a: for (var d = 0; d < b.length; d++) for (var e = b[d], f = 0; f < this._source.length; f++) {
                        var g = this._source[f];
                        if (e.name == g.name && e.size == g.size) {
                            c.push(d);
                            continue a;
                        }
                    }
                    c.sort(function(a, b) {
                        return b - a;
                    });
                    for (var h = 0; h < c.length; h++) b.splice(c[h], 1);
                    this._source = this._source.concat(b);
                } else this._source = b;
                this._sliceSourceLength(), this._callback("listChange", this._source), this.setting.autostart && this.upload(), 
                this._dom.value = null;
            }, HTML5MUP.prototype._queueUpload = function() {
                var a = this, b = this._source[this._uploadingFlag], c = _getXMLHttpRequest(), d = {
                    id: 0
                };
                return _isValidExtType(b.name) ? b.size > this.setting.max_filesize ? (this._callback("error", b, HTML5MUP.ERR_FILESIZE, this.setting.i18n_err_size_pre + this.setting.max_filesize / 1024 + "kb"), 
                this._serverRtnCache.push(d), void this._next()) : (c.upload ? c.upload.onprogress = function(c) {
                    c.lengthComputable && a._callback("progress", b, c.loaded / c.total);
                } : this._callback("progress", this._source[this._uploadingFlag], .5), c.onload = function(e) {
                    var f = e ? e.target : c;
                    if (200 == f.status) {
                        try {
                            d = _getJSON(c.responseText);
                        } catch (g) {
                            return void a._callback("error", b, HTML5MUP.ERR_JSONFORMAT, "服务器返回值格式错误");
                        }
                        0 === parseInt(d.resultCode) ? a._callback("success", b, a.setting.i18n_up_upload_ok) : a._callback("error", b, d.resultCode, d.describe), 
                        a._serverRtnCache.push(d), a._next();
                    } else 404 == f.status && (a._callback("error", b, 404, a.setting.i18n_up_ioerror), 
                    a._serverRtnCache.push(d), a._next());
                }, c.open(this.setting.method || "post", this.setting.url, !0), void (this.setting._is_debug_env || !_isWifi && MOA.env.android ? _doBase64Up(b, c, this.setting.base64_quality, this) : _doCommonUp(b, c))) : (this._callback("error", b, HTML5MUP.ERR_FILETYPE, this.setting.i18n_err_type), 
                this._serverRtnCache.push(d), void this._next());
            }, HTML5MUP.prototype._next = function() {
                if (this.setting.multiple) {
                    if (++this._uploadingFlag <= this._source.length - 1) return void this._queueUpload();
                    this._finish();
                } else this._finish();
            }, HTML5MUP.prototype._finish = function() {
                if (this._uploadingFlag = -1, this.enable(!1), this._submitBtn && (this._submitBtn.disabled = !0), 
                "callback_queueloaded" in this.setting) {
                    for (var a = [], b = this._serverRtnCache.length, c = 0; b > c; c++) a.push(this._serverRtnCache[c]);
                    this._callback("queueLoaded", a);
                }
                "callback_reactive" in this.setting && this._callback("reactive"), this.reactive();
            }, HTML5MUP.prototype._sliceSourceLength = function() {
                var a = "max_filecount" in this.setting ? this.setting.max_filecount : 10;
                this._source.length > a && (this._source = this._source.slice(0, a), this._callback("notice", "保留选择队列中的前" + a + "个"));
            }, HTML5MUP.prototype._callback = function() {
                for (var args = [], _i = 0; _i < arguments.length - 0; _i++) args[_i] = arguments[_i + 0];
                for (var i = 0; i < arguments.length; i++) args[i] = arguments[i];
                var type = args.shift();
                if (args.push(this._uploadingFlag), "callback_" + type.toLowerCase() in this.setting) {
                    var func = this.setting["callback_" + type.toLowerCase()];
                    "string" == typeof func && (func = eval(func)), func.apply(null, args);
                }
            }, HTML5MUP.ERR_FILETYPE = 9001, HTML5MUP.ERR_FILESIZE = 9002, HTML5MUP.ERR_JSONFORMAT = 9003, 
            HTML5MUP;
        }();
        _file.HTML5MUP = HTML5MUP;
    }(MOA.file || (MOA.file = {}));
    var file = MOA.file;
}(MOA || (MOA = {}));
