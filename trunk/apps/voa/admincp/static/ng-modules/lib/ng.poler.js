/**
 * @fileOverview poler 项目公共框架模块定义
 * @author 王利平
 * @version 0.1
 */

/**
 * @namespace ng
 */

/**
 * @name ng.poler
 * @namespace ng.poler
 */

angular.module('ng.poler.util',[]);

angular.module('ng.poler',[
    'ngRoute','ui.router','ng.poler.util'
]);



/**
 * Created by three on 15/8/10.
 */
(function (app) { app
    .provider('Where',['$and',function ($and) {
      /**
       * [opt, left, right]
       * @returns {*}
       */
      var OPT = {
        AND:'&&',
        OR: '||',
        EQ: '==',
        NEQ: '!=',
        LT: '<',
        LTE: '<=',
        GT: '>',
        GTE: '>=',
        IN: 'in',
        NIN: 'not in',
        NOT: '!',
        EXIST:'exist',
        NEXIST:'not exist',
        IS: 'is'
      };
      var where = {
        format: function (logicNode) {

          return JSON.stringify(logicNode);
        }
      };
      var _extend = this.extend = function (opt, flag, funcName) {
        OPT[opt] = flag;
        where[funcName] = function (left, right) {
          var _opt = opt;
          return [_opt, left, right];
        }
      };

      this.$get = function () {
        where.extend = _extend;
        return where;
      };
    }])
;
})(angular.module('ng.poler.util')
        .constant('$and',function (left, right) {
          if(!left && !right) {
            return null;
          }
          if(!left) {
            return right;
          }
          if(!right) {
            return left;
          }
          return ['AND', left, right];
        })
        .constant('$or', function (left, right) {
          if(!left && !right) {
            return null;
          }
          if(!left) {
            return right;
          }
          if(!right) {
            return left;
          }
          return ['OR', left, right];
        })
        .constant('$eq', function (left, right) {
          return ['EQ', left, right];
        })
        .constant('$lt', function (left, right) {
          return ['LT', left, right];
        })
        .constant('$lte', function (left, right) {
          return ['LTE', left, right];
        })
        .constant('$gt', function (left, right) {
          return ['GT', left, right];
        })
        .constant('$gte', function (left, right) {
          return ['GTE', left, right];
        })
        .constant('$in', function (left, right) {
          return ['AND', left, right];
        })
        .constant('$not', function (left) {
          return ['NOT', left];
        })
        .constant('$exist', function (left, right) {
          return ['EXIST', left, right];
        })
);
/**
 * Created by three on 15/6/16.
 */
(function (app, window) {
    'use strict';

    /*
     * Generate a random uuid.
     *
     * USAGE: UUIDGenerator.uuid(length, radix)
     *   length - the desired number of characters
     *   radix  - the number of allowable values for each character.
     *
     * EXAMPLES:
     *   // No arguments  - returns RFC4122, version 4 ID
     *   >>> UUIDGenerator.uuid()
     *   "92329D39-6F5C-4520-ABFC-AAB64544E172"
     *
     *   // One argument - returns ID of the specified length
     *   >>> UUIDGenerator.uuid(15)     // 15 character ID (default base=62)
     *   "VcydxgltxrVZSTV"
     *
     *   // Two arguments - returns ID of the specified length, and radix. (Radix must be <= 62)
     *   >>> UUIDGenerator.uuid(8, 2)  // 8 character ID (base=2)
     *   "01001010"
     *   >>> UUIDGenerator.uuid(8, 10) // 8 character ID (base=10)
     *   "47473046"
     *   >>> UUIDGenerator.uuid(8, 16) // 8 character ID (base=16)
     *   "098F4D35"
     */
    var CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');

    window.UUIDGenerator = { };
    window.UUIDGenerator.uuid = function (len, radix) {
        var chars = CHARS, uuid = [], i;
        radix = radix || chars.length;

        if (len) {
            // Compact form
            for (i = 0; i < len; i++) uuid[i] = chars[0 | Math.random()*radix];
        } else {
            // rfc4122, version 4 form
            var r;

            // rfc4122 requires these characters
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';

            // Fill in random data.  At i==19 set the high bits of clock sequence as
            // per rfc4122, sec. 4.1.5
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random()*16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }

        return uuid.join('');
    };

    // A more performant, but slightly bulkier, RFC4122v4 solution.  We boost performance
    // by minimizing calls to random()
    window.UUIDGenerator.uuidFast = function() {
        var chars = CHARS, uuid = new Array(36), rnd=0, r;
        for (var i = 0; i < 36; i++) {
            if (i==8 || i==13 ||  i==18 || i==23) {
                uuid[i] = '-';
            } else if (i==14) {
                uuid[i] = '4';
            } else {
                if (rnd <= 0x02) rnd = 0x2000000 + (Math.random()*0x1000000)|0;
                r = rnd & 0xf;
                rnd = rnd >> 4;
                uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
            }
        }
        return uuid.join('');
    };

    // A more compact, but less performant, RFC4122v4 solution:
    window.UUIDGenerator.uuidCompact = function() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
    };

    /**
     * angular 模块封装
     */
    app.provider('UUIDGenerator',[function () {

        var self = this;

        self.$get = [function () {
            return window.UUIDGenerator;
        }]
    }])
})(angular.module('ng.poler.util'), window);
/**
 * 出发ios9但页面刷新工具
 * 现在使用ajax请求刷新
 */
(function (app, window) {
    'use strict';

    app.provider('Ios9Refresh',[function () {

        var self = this;
        var refreshUrl = 'empty-req.json';

        self.refreshUrl = function (url) {
            refreshUrl = url;
        };

        self.$get = ['$http',function($http){
            return {
                ajaxRefresh:function(){
                    $http.get(refreshUrl).
                        success(function(data, status, headers, config) {
                            // this callback will be called asynchronously
                            // when the response is available
                        }).
                        error(function(data, status, headers, config) {
                            // called asynchronously if an error occurs
                            // or server returns response with an error status.
                        });
                }
            };
        }];
    }]);
})(angular.module('ng.poler.util'), window);
/**
 * Created by three on 15/6/16.
 */


(function (app) {
    'use strict';

    /**
     * angularJs 工厂方法provider
     * 用于动态创建 controller/service/filter/directive
     */
    app.provider('Factories', ["$controllerProvider", "$compileProvider", "$filterProvider", "$routeProvider", "$provide",
        function ($controllerProvider, $compileProvider, $filterProvider, $routeProvider, $provide) {
            var factory = this;
            factory.register =
            {
                controller: $controllerProvider.register,
                directive: $compileProvider.directive,
                filter: $filterProvider.register,
                factory: $provide.factory,
                service: $provide.service
            };

            factory.ServiceFactory = {
                create: function (name, func) {
                    factory.register.factory(name, func);
                }
            };
            factory.ControllerFactory = {
                create: function (name, func) {
                    factory.register.controller(name, func);
                }
            };
            factory.DirectiveFactory = {
                create: function (name, func) {
                    factory.register.directive(name, func);
                }
            };
            factory.FilterFactory = {
                create: function (name, func) {
                    factory.register.filter(name, func);
                }
            };

            this.$get = function () {
                return function () {
                    return {
                        ServiceFactory: factory.ServiceFactory,
                        ControllerFactory: factory.ControllerFactory,
                        DirectiveFactory: factory.DirectiveFactory,
                        FilterFactory: factory.FilterFactory
                    }
                }
            }
        }
    ]);
})(angular.module('ng.poler.util'));

/**
 * Created by three on 14-11-4.
 */
'use strict';

(function (app) { app
    //防抖处理
    .factory('$debounce', ['$rootScope', '$browser', '$q', '$exceptionHandler',
        function ($rootScope, $browser, $q, $exceptionHandler) {
            var deferreds = {},
                methods = {},
                uuid = 0;

            function debounce(fn, delay, invokeApply) {
                var deferred = $q.defer(),
                    promise = deferred.promise,
                    skipApply = (angular.isDefined(invokeApply) && !invokeApply),
                    timeoutId, cleanup,
                    methodId, bouncing = false;

                // check we dont have this method already registered
                angular.forEach(methods, function (value, key) {
                    if (angular.equals(methods[key].fn, fn)) {
                        bouncing = true;
                        methodId = key;
                    }
                });

                // not bouncing, then register new instance
                if (!bouncing) {
                    methodId = uuid++;
                    methods[methodId] = {fn: fn};
                } else {
                    // clear the old timeout
                    deferreds[methods[methodId].timeoutId].reject('bounced');
                    $browser.defer.cancel(methods[methodId].timeoutId);
                }

                var debounced = function () {
                    // actually executing? clean method bank
                    delete methods[methodId];

                    try {
                        deferred.resolve(fn());
                    } catch (e) {
                        deferred.reject(e);
                        $exceptionHandler(e);
                    }

                    if (!skipApply) $rootScope.$apply();
                };

                timeoutId = $browser.defer(debounced, delay);

                // track id with method
                methods[methodId].timeoutId = timeoutId;

                cleanup = function (reason) {
                    delete deferreds[promise.$$timeoutId];
                };

                promise.$$timeoutId = timeoutId;
                deferreds[timeoutId] = deferred;
                promise.then(cleanup, cleanup);

                return promise;
            }


            // similar to angular's $timeout cancel
            debounce.cancel = function (promise) {
                if (promise && promise.$$timeoutId in deferreds) {
                    deferreds[promise.$$timeoutId].reject('canceled');
                    return $browser.defer.cancel(promise.$$timeoutId);
                }
                return false;
            };

            return debounce;
        }]);
})(angular.module('ng.poler.util'));




(function (app, window) {
    /**
     * 客户端检测代码
     */
    window.client_browser = (function () {
        // 呈现引擎
        var engine = {
            ie:0,
            gecko:0,
            webkit:0,
            khtml:0,
            opera:0,
            // 完整的版本号
            ver: null
        };

        // 浏览器
        var browser = {
            // 主要浏览器
            ie:0,
            firefox:0,
            safari:0,
            konq:0,
            opera:0,
            chrome:0,
            wx:0,
            // 具体的版本号
            ver:null
        };

        // 平台、设备和操作系统
        var system = {
            win:false,
            mac:false,
            x11:false,

            // 移动设备
            iphone:false,
            ipod:false,
            ipad:false,
            ios:false,
            android:false,
            nokiaN:false,
            winMobile:false,

            //游戏系统
            wii:false,
            ps:false
        };

        // 检测呈现引擎和浏览器
        var ua = navigator.userAgent;
        if(window.opera) {
            engine.ver = browser.ver = window.opera.version();
            engine.opera = browser.opera = parseFloat(engine.ver);
        } else if(/AppleWebKit\/(\S+)/.test(ua)) {
            engine.ver = RegExp["$1"];
            engine.webkit = parseFloat(engine.ver);

            // 确定是 chrome 还是 safari
            if(/Chrome\/(\S+)/.test(ua)) {
                browser.ver = RegExp["$1"];
                browser.chrome = parseFloat(browser.ver);
            } else if(/Version\/(\S+)/.test(ua)) {
                browser.ver = RegExp["$1"];
                browser.safari = parseFloat(browser.ver);
            } else {
                // 近似地确定版本号
                var safariVersion = 1;
                if(engine.webkit<100) {
                    safariVersion = 1;
                } else if(engine.webkit<312) {
                    safariVersion = 1.2;
                } else if(engine.webkit<412) {
                    safariVersion = 1.3;
                } else {
                    safariVersion = 2;
                }
                browser.safari = browser.ver = safariVersion;
            }
        } else if(/KHTML\/(\S+)/.test(ua) || /Konqueror\/([^;]+])/.test(ua)) {
            engine.ver = browser.ver = RegExp["$1"];
            engine.khtml = browser.konq = parseFloat(engine.ver);
        } else if(/rv:([^\)]+)\) Gecko\/\d{8}/.test(ua)) {
            engine.ver = RegExp["$1"];
            browser.gecko = parseFloat(engine.ver);

            // 确定是否是firefox
            if(/Firefox\/(\S+)/.test(ua)) {
                browser.ver = RegExp["$1"];
                browser.firefox = parseFloat(browser.ver);
            }
        } else if(/MSIE ([^;]+)/.test(ua)) {
            engine.ver = browser.ver = RegExp["$1"];
            engine.ie = browser.ie = parseFloat(engine.ver);
        }

        // 微信检查
        if(/MicroMessenger\/([\d\.]+)/i.test(ua)) {
            browser.ver = RegExp["$1"];
            browser.wx = 'micromessenger';
        }

        // 坚持浏览器
        browser.ie = engine.ie;
        browser.opera = engine.opera;

        // 检测平台
        var p = navigator.platform;
        system.win = p.indexOf("Win") >= 0;
        system.mac = p.indexOf("Mac") >= 0;
        system.x11 = (p=="X11") || (p.indexOf("Linux")==0);

        // 检测 windows 操作版本
        if(system.win) {
            if(/Win(?:dows )?([^do]{2})\s?(\d+\.\d+)?/.test(ua)) {
                if(RegExp["$1"]=="NT") {
                    switch (RegExp["$2"]) {
                        case "5.0":
                            system.win = "2000";
                            break;
                        case  "5.1":
                            system.win = "XP";
                            break;
                        case "6.0":
                            system.win = "vista";
                            break;
                        case "6.1":
                            system.win = "7";
                            break;
                        default :
                            system.win = "NT";
                            break;
                    }
                } else if(RegExp["$1"] == "9x") {
                    system.win = "ME";
                } else {
                    system.win = RegExp["$1"];
                }
            }
        }

        // 检测移动设备
        system.iphone = ua.indexOf("iPhone") > -1;
        system.ipod = ua.indexOf("iPod") > -1;
        system.ipad = ua.indexOf("iPad") > -1;
        system.nokiaN = ua.indexOf("NokinaN") > -1;

        // windows mobile
        if(system.win == "CE") {
            system.winMobile = system.win;
        } else if(system.win == "Ph") {
            if(/Window Phone OS (\d+.\d+)/.test(ua)) {
                system.win = "Phone";
                system.winMobile = parseFloat(RegExp["$1"]);
            }
        }

        // 检测 iOS 版本
        if(system.iphone && ua.indexOf("Mobile")>-1) {
            if(/CPU (?:iPhone)?[ ]?OS (\d+_\d+)/.test(ua)) {
                system.ios = parseFloat(RegExp.$1.replace("_","."));
            } else {
                system.ios = 2; // 不能真正检测出来，所以只能猜测
            }
        }

        // 检测 android 版本
        if(/Android (\d+\.\d+)/.test(ua)) {
            system.android = parseFloat(RegExp.$1);
        }

        // 游戏系统
        system.wii = ua.indexOf("Wii") > -1;
        system.ps = /playstation/i.test(ua);

        // 返回对象
        return {
            engine: engine,
            browser: browser,
            system: system
        }
    })();
    app
    .provider('Browser',[function () {
        var self = this;
        self.$get = [function () {
            return window.client_browser;
        }]
    }])

})(angular.module('ng.poler.util'), window);
'use strict';
/**
 * Created by three on 14-7-15.
 */
(function (app) {
    app
        .factory('TokenInterceptor', ["$q", "$window", function ($q, $window) {
            return {
                request: function (config) {
                    config.headers = config.headers || {};
                    return config;
                },
                response: function (response) {
                    return response;
                }
            };
        }])
        .factory('TimestampMarker', ['$q',function ($q) {
            var recordReqLog = function (response) {
                console.info(' 请求时间：[' + (response.config.responseTimestamp - response.config.requestTimestamp) + '] ' + response.config.url);
            };
            return {
                request: function (config) {
                    config.requestTimestamp = new Date().getTime();
                    return config;
                },
                response: function (response) {
                    response.config.responseTimestamp = new Date().getTime();
                    recordReqLog(response);
                    return response;
                },
                'responseError': function (response) {
                    if (response.config) {
                        response.config.responseTimestamp = new Date().getTime();
                        recordReqLog(response);
                    }
                    return $q.reject(response);
                }
            };
        }])
        .factory('HttpErrorInterceptor', ["$injector",'$q','InterceptorConfig', function ($injector,$q,InterceptorConfig) {
            return {
                'request': function (request) {
                    var res = undefined;
                    if(angular.isString(InterceptorConfig.requestHandler)) {
                        var handler = $injector.get(InterceptorConfig.requestHandler);
                        res = handler.handle(request);
                    }
                    if(angular.isDefined(res)) {
                        return res;
                    } else {
                        return request;
                    }
                },
                'requestError': function (rejection) {
                    var res = undefined;
                    if(angular.isString(InterceptorConfig.requestErrorHandler)) {
                        var handler = $injector.get(InterceptorConfig.requestErrorHandler);
                        res = handler.handle(response);
                    }
                    if(angular.isDefined(res)) {
                        return res;
                    } else {
                        return $q.reject(rejection);
                    }
                },
                'response': function (response) {
                    var res = undefined;
                    if(angular.isString(InterceptorConfig.responseHandler)) {
                        var handler = $injector.get(InterceptorConfig.responseHandler);
                        res = handler.handle(response);
                    }
                    if(angular.isDefined(res)) {
                        return res;
                    } else {
                        return response;
                    }
                },
                'responseError': function (response) {
                    var res = undefined;
                    if(angular.isString(InterceptorConfig.responseErrorHandler)) {
                        var handler = $injector.get(InterceptorConfig.responseErrorHandler);
                        res = handler.handle(response);
                    }

                    if(angular.isDefined(res)) {
                        return res;
                    } else {
                        return $q.reject(response);
                    }
                }
            };
        }])
        .provider('InterceptorConfig',['$httpProvider',function ($httpProvider) {
            var config = {
                requestHandler: function () {},
                requestErrorHandler: function(){},
                responseHandler: function () {},
                responseErrorHandler: function () {}
            };
            this.$get = [function () {
                return config;
            }];

            /**
             * 配置默认请求拦截器处理handler
             * @param requestHandler
             * @returns {*}
             */
            this.configRequestHandler = function (requestHandler) {
                config.requestHandler = requestHandler;
                return this;
            };
            this.configRequestErrorHandler = function (requestErrorHandler) {
                config.requestErrorHandler = requestErrorHandler;
                return this;
            };
            this.configResponseHandler = function (responseHandler) {
                config.responseHandler = responseHandler;
                return this;
            };
            this.configResponseErrorHandler = function (responseErrorHandler) {
                config.responseErrorHandler = responseErrorHandler;
                return this;
            };

            /**
             * 加入外部拦截器
             * @param interceptors
             */
            this.push = function (interceptors) {
                if(angular.isString(interceptors)) {
                    $httpProvider.interceptors.push(interceptors);
                } else if(angular.isArray(interceptors)) {
                    var i;
                    for(i=0; i<interceptors.length; i++) {
                        $httpProvider.interceptors.push(interceptors[i]);
                    }
                }
            };

        }])
    ;
})(angular.module('ng.poler').config(['$httpProvider', function ($httpProvider) {
    // 拦截器配置
    $httpProvider.interceptors.push('TimestampMarker');
    $httpProvider.interceptors.push('HttpErrorInterceptor');

    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.headers.patch['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.withCredentials = true;

    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function (obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for (name in obj) {
            value = obj[name];

            if (value instanceof Array) {
                for (i = 0; i < value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value instanceof Object) {
                for (subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function (data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
}]));



/**
 * @fileOverview 和页面相关的操作组件，比如页面间跳转，页面内部跳转，返回顶部等操作
 * @author 王利平
 * @version 0.1
 */
(function (app) {
    app
        .provider('Page',[function () {
            var _self = this;
            this.setTitle = function (title) {
                setTimeout(function () {
                    document.title = title;
                    var $body = $('body');
                    // hack在微信等webview中无法修改document.title的情况
                    var $iframe = $('<iframe src="/favicon.ico" style="visibility: hidden;"></iframe>').on('load', function () {
                        setTimeout(function () {
                            $iframe.off('load').remove();
                        }, 0)
                    }).appendTo($body)
                });
            };

            this.$get = ['$timeout','$location','$anchorScroll','$state','$route','$document','$window',function ($timeout,$location,$anchorScroll,$state,$route,$document,$window) {
                var service = {};
                /**
                 * 设置显示标题
                 * @param title
                 */
                service.setTitle = _self.setTitle;

                /**
                 * 页面跳转方法
                 * @param state
                 * @param params
                 * @param config
                 */
                service.goState = function (state, params, config) {
                    $state.go(state, params, config);
                };

                /**
                 * 页面跳转，支持自带查询参数
                 * @param url
                 * @param params
                 * @param hash
                 */
                service.goPage = function (url, params, hash) {
                    if (!hash) {
                        hash = '';
                    }
                    return $location.path(url).search(params).hash(hash);
                };

                /**
                 * 页面内部跳转
                 * @param id
                 */
                service.goPos = function (id) {
                    $location.hash(id);

                    if(!id) {
                        service.goTop();
                        return;
                    }
                    $anchorScroll();
                };

                /**
                 * 返回页面顶部
                 */
                service.goTop = function () {
                    $document.scrollTop(0);
                };

                /**
                 * 页面状态刷新
                 */
                service.refreshState = function () {
                    $state.go($state.current, {}, {reload: true});
                };
                /**
                 * 刷新页面所有内容
                 * @param clearCache {boolean} 是否清楚缓存
                 */
                service.refreshPage = function (clearCache) {
                    $window.location.reload(!!clearCache);
                };
                return service;
            }];
        }])
        .directive('goTop',[function () {

            return {
                restrict : 'A',
                compile:function(element, attrs){
                    return {
                        pre: function(scope,element,attr){

                            var oTop = element.find(attr['goTopBtn']);  //返回顶部dom
                            var maxTop = parseInt(attr['goTop'] || 300);  //最大滚动距离

                            if(oTop.length==0) {
                                return;
                            }

                            /**
                             * 隐藏返回顶部
                             */
                            scope.hide = function () {
                                oTop.hide();
                            };
                            /**
                             * 显示返回顶部
                             */
                            scope.show = function () {
                                oTop.show();
                            };

                            /**
                             * 监听滚动事件
                             */
                            element.scroll(function(event){
                                if($(this).scrollTop() >= maxTop){
                                    scope.show();
                                } else {
                                    scope.hide();
                                }
                            });

                            scope.hide();

                            /**
                             * 返回顶部点击事件
                             */
                            oTop.delegate(this,'click || touchstart',function(event) {
                                event.preventDefault();
                                event.stopPropagation();
                                element.scrollTop(0);
                                scope.hide();

                            });


                            /**
                             * 销毁事件
                             */
                            scope.$on('$destroy', function () {
                                oTop.remove();
                            });
                        },
                        post: function(scope,element,attr){

                        }
                    }
                }
            }

        }])
    ;
})(angular.module('ng.poler'));
/**
 * @fileOverview poler 日志服务
 * @author 王利平
 * @version 0.1
 */

/**
 * 重写 window.console 实现
 * <li>提供日志级别和日志目标输出，只能删除比配置级别更低的日志。</li>
 * <li>提供 alert、console、none、server等日志输出目标</li>
 * @name console
 * @memberof ng.poler
 * @namespace console
 */
(function (window) {
    /**
     * //@memberof ng.poler.console
     * @description 定义日志输出方式
     * @class
     *
     */
    var LogTargets = {
        /**
         * @description alert 输出
         */
        alert: function (msg, stack) {
            alert(msg[0]+"  = at =  "+stack);
        },
        /**
         * @description console 输出
         * @param msg {String} 日志信息
         * @param stack {String} 日志输出位置调用栈
         */
        console: function (msg, stack) {
            if(!window.hasOwnProperty('old_console')) {
                window.old_console = window.console;
            }
            window.old_console.log.apply(window.old_console,msg);
            if(stack) {
                window.old_console.log(stack)
            }
        },
        /**
         * @description 不输出日志
         */
        none: function () {

        }
    };
    var target = LogTargets.console;
    var level = 4; // 0:log 1:info 2:warn 3:error 4:debug
    /**
     * 内部使用日志输入操作
     * @private
     */
    var __print = function () {
        try {
            throw new Error();
        } catch(e) {
            var stack = e.stack.replace(/Error.*\n/,'').split('\n');
            stack.shift();
            stack.shift();
            stack = stack.join('\n');
            if(level<=3) {
                stack = undefined;
            }
            target.apply(target, _.toArray(arguments).concat(stack));
        }
    };
    /**
     * @description 重写console工具
     * //@memberof ng.poler.console
     * @class
     */
    var PolerConsole = {

        /**
         * 日志配置
         * @memberof ng.poler.console
         * @function config
         * @param outputTarget 日志输出目标 [参考 LogTargets]{@link ng.poler.logger.LogTargets}
         * @param level 日志输出级别  0:log 1:info 2:warn 3:error 4:debug
         * @returns {PolerConsole}
         */
        config: function (outputTarget, outputLevel) {
            if (outputTarget == 'alert') {
                target = LogTargets.alert;
            } else if (outputTarget == 'console') {
                target = LogTargets.console;
            } else {
                target = LogTargets.none;
            }
            if(typeof outputLevel === 'number') {
                level = outputLevel;
            }
            return this;
        },

        /**
         * log 级别日志输出，可以接受任意参数
         * @memberof ng.poler.console
         * @function log
         * @returns {PolerConsole}
         */
        log: function () {
            __print(arguments);
            return this;
        },
        /**
         * info 级别日志输出，可以接受任意参数
         * @memberof ng.poler.console
         * @function info
         * @returns {PolerConsole}
         */
        info: function () {
            if(1<=level) {
                __print(arguments);
            }
            return this;
        },
        /**
         * warn 级别日志输出，可以接受任意参数
         * @memberof ng.poler.console
         * @function warn
         * @returns {PolerConsole}
         */
        warn: function () {
            if(2<=level) {
                __print(arguments);
            }
            return this;
        },
        /**
         * debug 级别日志输出，可以接受任意参数
         * @memberof ng.poler.console
         * @function debug
         * @returns {PolerConsole}
         */
        debug: function () {
            if(3<=level) {
                __print(arguments);
            }
            return this;
        },
        /**
         * error 级别日志输出，可以接受任意参数
         * @memberof ng.poler.console
         * @function error
         * @returns {PolerConsole}
         */
        error: function () {
            if(4<=level) {
                __print(arguments);
            }
            return this;
        },
        /**
         * 还原默认console
         * @memberof ng.poler.console
         * @function useOld
         * @returns {PolerConsole}
         */
        useOld: function () {
            window.console = window.old_console;
            return this;
        }
    };

    /**
     * @description 使用新的console对象
     * @function userNew
     * @memberof ng.poler.console
     * @param consoleObj 新的 console 对象
     */
    window.console.useNew = function (consoleObj) {
        window.old_console = window.console;
        window.console = consoleObj || PolerConsole;
    };
    window.console.config = function () {
    };
    //window.console.useNew(PolerConsole);
})(window);

/**
 * @fileOverview 国际化功能
 * @author 王利平
 * @version 0.1
 */


/**
 * @name localizeProvider
 * @memberof ng.poler
 * @namespace localizeProvider
 */
(function (app) {
    'use strict';
    app
    .provider('localize', [function () {
        var _selfProvider = this;
        /**
         * 语言定义配置
         */
        var langConfig = {};

        /**
         * @function configLang
         * @memberof ng.poler.localizeProvider
         * @description localizeProvider 配置新语言
         * @author three
         * @param name 语言名称
         * @param data 语言翻译数据
         * @returns {localizeProvider}
         */
        this.configLang = function (name, data) {
            if(angular.isString(name)) {
                langConfig[name] = data;
            }

            return this;
        };

        this.$get = ['$http', '$rootScope','$q', function ($http, $rootScope, $q) {

            // 保存当前语言数据
            var currentLang = {};
            
            /**
             * @description localize service
             * @namespace localize
             */
            var localize = {

                /**
                 * @function configLang
                 * @memberof localize
                 * @description localize 配置新语言
                 * @author three
                 * @param name 语言名称
                 * @param data 语言翻译数据
                 * @returns {localize}
                 */
                configLang: _selfProvider.configLang,
                /**
                 * @function setLang
                 * @memberof localize
                 * @author three
                 * @description 设置当前语言
                 * @param langName 语言代码或名称
                 * @returns {promise}
                 */
                setLang: function (langName) {
                    var q = $q.defer();
                    if(angular.isUndefined(langName) || !langConfig.hasOwnProperty(langName)) {
                        console.log("系统不支持语言：", langName);
                        return false;
                    }

                    var langData = langConfig[langName];

                    if(angular.isFunction(langData)) {
                        langData = langData();
                    }

                    if(angular.isString(langData)) {
                        // 加载语言数据
                        $http.get(langData, {cache: false})
                            .success(function (data) {
                                currentLang.data = data;
                                currentLang.name = langName;
                                $rootScope.$broadcast('localizeLanguageChanged');
                                q.resolve(data);
                            }).error(function (error) {
                                console.log('Error updating language!', error);
                                q.reject(error);
                            });
                    } else if(angular.isObject(langData) && !angular.isArray(langData) && !angular.isDate(langData)) {
                        // 加载本地数据
                        // TODO 处理angular的promise对象
                        currentLang.data = langData;
                        currentLang.name = langName;
                        $rootScope.$broadcast('localizeLanguageChanged');
                        q.resolve(langData);
                    } else {
                        q.reject('语言配置信息错误', langData);
                    }
                    return q.promise;
                },
                /**
                 * @function localizeText
                 * @memberof localize
                 * @author three
                 * @description 翻译到当前语言
                 * @param sourceText 翻译源字符船
                 * @returns 翻译过后的数据
                 */
                localizeText: function (sourceText) {
                    if(currentLang.data) {
                        var s = currentLang.data[sourceText];
                        if(s) {
                            return s;
                        }
                    }
                    return sourceText;
                }
            };

            return localize;
        }];
    }])

/**
 * 语言翻译指令
 */
    .directive('localize', ['localize','$parse','$interpolate', function (localize,$parse,$interpolate) {
        var localizeFun = function (scope, element, attrs) {

            if(!attrs.hasOwnProperty('localize')) { // dom使用方式
                var text;
                if(attrs['data']) {
                    text = $parse(attrs['data'])(scope);
                } else {
                    if(!attrs['originalText']) {
                        attrs['originalText'] = $interpolate(element.text())
                    }
                    text = attrs['originalText'](scope);
                }

                var localizedText = localize.localizeText(text);
                element.text(localizedText);
            } else { // 属性使用方式
                var text;
                if(attrs['localize']) {
                    text = $parse(attrs['localize'])(scope);
                } else {
                    if(!attrs['originalText']) {
                        if (element.is('input,textarea')) {
                            attrs['originalText'] = $interpolate(element.attr('placeholder'));
                        } else {
                            attrs['originalText'] = $interpolate(element.text())
                        }
                    }
                    text = attrs['originalText'](scope);
                }

                var localizedText = localize.localizeText(text);
                if (element.is('input, textarea')) {
                    element.attr('placeholder', localizedText)
                } else {
                    element.text(localizedText);
                }
            }
        };
        return {
            restrict: 'EA',
            link: function (scope, element, attrs) {

                localizeFun(scope, element, attrs);

                scope.$on('localizeLanguageChanged', function () {
                    localizeFun(scope, element, attrs);
                });
            }
        }
    }])
/**
 * 语言过滤器
 */
    .filter('localize', ['localize', function (localize) {
        return function (text) {
            return localize.localizeText(text);
        }
    }])
;
})(angular.module('ng.poler'));


/**
 * Created by three on 15/9/24.
 * poler 函数式库
 */

/**
 * app 启动
 */
function existy(val) {
    return val!=null;
}
function truthy(x) {
    return (x!==false) && existy(x);
}
function always(d) {
    return function () {
        return d;
    };
}
function cat() {
    var head = _.first(arguments);
    if(existy(head)) {
        return head.concat.apply(head, _.rest(arguments));
    } else {
        return [];
    }
}
function construct(head, tail) {
    return cat(head, _.toArray(tail));
}
function mapcat(fun, coll) {
    return cat.apply(null, _.map(coll, fun));
}
/**
 * 验证执行器
 */
function checker(/* validators */) {
    var validators = _.toArray(arguments);

    return function (obj) {
        return _.reduce(validators, function (errs, check) {
            if(check(obj)) {
                return errs;
            } else {
                return _.chain(errs).push(check.message).value();
            }
        },[]);
    };
}
/**
 * 验证函数构造器
 * @param message
 * @param fun
 * @returns {Function}
 */
function validator(message, fun) {
    var f = function (/* args */) {
        return fun.apply(fun, arguments);
    };
    f['message'] = message;
    return f;
}
/**
 * 分发器
 */
function dispatch(/* funs */) {
    var funs = _.toArray(arguments);
    var size = funs.length;

    return function (target /*, args */) {
        var ret = undefined;
        var args = _.rest(arguments);

        for(var funIndex=0; funIndex<size; funIndex++) {
            var fun = funs[funIndex];
            ret = fun.apply(fun, [target].concat(args));

            if(existy(ret)) {
                return ret;
            }
        }
        return ret;
    };
}

function condition(/* validators */) {
    var validators = _.toArray(arguments);
    return function (args) {
        var errors = mapcat(function (isValid) {
            return truthy(isValid(args))?[]:[isValid.message];
        }, validators);

        if(!_.isEmpty(errors)) {
            throw new Error(errors.join(" "));
        }

        return true;
    }
}

/**
 * 条件执行
 * @param isFun
 * @param action
 * @returns {Function}
 */
function doWhen(condition,action) {
    return function () {
        var f = false;
        try{
            f = condition.apply(condition, arguments);
        }catch(e) {
            console.error(e);
        }

        if(truthy(f)) {
            return action.apply(action, arguments);
        } else {
            return undefined;
        }
    }
}
/**
 * Created by three on 15/6/16.
 */
(function (app, window) {
    app
        .provider('RouterRole', ['$locationProvider',function ($locationProvider) {
            var self = this;
            var config = {
                pathVar:'funcPath',
                commonViews_404:'app/components/404/views/404.html',
                componentsPath: 'app/components',
                viewPath:'views',
                defaultFuncView:'index'
            };
            this.hashPrefix = function (prefix) {
                prefix = prefix || '!';
                $locationProvider.hashPrefix(prefix);
            };
            this.config = function (conf) {
                if(conf.pathVar) {
                    config.pathVar = conf.pathVar;
                }
                if(conf.commonViews_404) {
                    config.commonViews_404 = conf.commonViews_404;
                }
                if(conf.componentsPath) {
                    config.componentsPath = conf.componentsPath;
                }
                if(conf.viewPath) {
                    config.viewPath = conf.viewPath;
                }
                if(conf.defaultFuncView) {
                    config.defaultFuncView = conf.defaultFuncView;
                }
            };
            this.parseFuncViewUrlFactory = function () {
                return function (params) {
                    return self.parseFuncViewUrl(params[config.pathVar]);
                };
            };
            this.parseFuncViewUrl = function (funcPath) {

                funcPath = decodeURI(decodeURIComponent(funcPath));
                funcPath = funcPath.replace(/\./ig, '/');
                // remove tailing slash
                funcPath = funcPath.replace(/\/$/, '');

                var path = '';
                do {
                    if (!funcPath) {
                        path = config.commonViews_404;
                        break;
                    }

                    // 解析查询参数
                    var temp = funcPath.split('?');

                    var searchParams = '';
                    if (temp.length == 2) {
                        searchParams = '?' + temp[1];
                    }

                    // 解析功能路劲
                    funcPath = temp[0];
                    var splitIndex = funcPath.lastIndexOf('/') || funcPath.length;
                    var modulePath = funcPath.substring(0, splitIndex);
                    var funcView = funcPath.substring(splitIndex + 1) || config.defaultFuncView;
                    if (!modulePath) {
                        modulePath = funcView;
                        funcView = config.defaultFuncView;
                    }
                    path = config.componentsPath+'/' + modulePath + '/'+config.viewPath+'/' + funcView + '.html';
                } while (false);

                return path;
            };
            this.parseFuncDepsFactory = function (funcPath) {
                return ['$http', '$stateParams', '$q', function ($http, $stateParams, $q) {
                    var defer = $q.defer();
                    defer.resolve();
                    return defer.promise;
                }];
            };
            this.$get = [function () {
                return null;
            }];
        }])
        .provider('Logger', [function () {
            this.config = function () {
                window.console.config(arguments);
            };

            this.$get = [function () {
                return window.console;
            }]
        }])
        .provider('PolerConfig',[function () {
            var _self = this;
            this.props = {};
            this.config = function(config) {
                angular.extend(_self.props, config);
            };
            this.$get = [function () {
                return _self.props;
            }]
        }])
    ;
})(angular.module('ng.poler'), window);
(function (app) {
    app
        .controller('PolerCtrl', ['$rootScope', '$state', '$location', '$stateParams', '$timeout', 'Page',
            function ($rootScope, $state, $location, $stateParams, $timeout, Page) {
                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;
                $rootScope.$location = $location;

                /**
                 * 设置显示标题
                 * @param title
                 */
                $rootScope.setTitle = Page.setTitle;

                /**
                 * 页面跳转方法
                 * @param state
                 * @param params
                 * @param config
                 */
                $rootScope.goState = Page.goState;

                /**
                 * 页面跳转，支持自带查询参数
                 * @param url
                 * @param params
                 * @param hash
                 */
                $rootScope.goPage = Page.goPage;

                /**
                 * 页面内部跳转
                 * @param id
                 */
                $rootScope.goPos = Page.goPos;

                /**
                 * 公共事件处理
                 */
                $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {

                });
                $rootScope.$on('$stateChangeSuccess', function () {

                });
                $rootScope.$on('$stateChangeError', function () {

                });
                $rootScope.$on('$viewContentLoading', function () {

                });
                $rootScope.$on('$viewContentLoaded', function () {

                });
            }])
    ;
})(angular.module('ng.poler'));

/**
 * @fileOverview 国际化功能
 * @author 王利平
 * @date 2015-09-09
 * @version 0.0.1
 */

/**
 * @name ApiUtil
 * @memberof ng.poler
 * @namespace ApiUtil
 */
(function (app) {
    app
        .factory('ApiUtil', ['$http', '$q', function ($http, $q) {
            var URL_PREFIX = '';

            var request_method = {
                /**
                 * @description ApiUtil 参数配置
                 * @function config
                 * @memberof ng.poler.ApiUtil
                 * @param {Object} config
                 *  {<br>
                 *      URL_PREFIX: {String} 请求地址统一前缀<br>
                 *  }<br>
                 */
                config: function (config) {
                    if (config.hasOwnProperty('URL_PREFIX') && angular.isDefined(config.URL_PREFIX)) {
                        URL_PREFIX = config.URL_PREFIX;
                    }
                },
                /**
                 * @description ApiUtil 请求方法
                 * @function request
                 * @memberof ng.poler.ApiUtil
                 * @param url {String} http 请求接口地址
                 * @param method {string} http 接口请求方法 (如 'GET', 'POST', 等)
                 * @param params {Object.<string|Object>} http 请求查询参数
                 * @param data {string|Object} http 请求发送数据
                 * @param config {object.<string|Object|function>} http 请求配置参数<br>
                 *  <b>headers {Object.<string|functions>} <br> </b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      字符串或者函数返回的字符串放入http请求头部发送到服务器。如果函数返回null，这个头部信息不会发送。<br>
                 *  <b>xsrfHeaderName {string} <br></b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      http 头部信息中 XSRF token 头部的名字<br>
                 *  <b>xsrfCookieName {string} <br></b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      cookie中包含 XSRF token 信息的名字<br>
                 *  <b>transformRequest {function(data, headersGetter)|Array.<function(data, headersGetter)>}<br></b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      请求头部和请求body 转换处理函数。会覆盖默认的转换处理函数。<br>
                 *  <b>transformResponse {function(data, headersGetter, status)|Array.<function(data, headersGetter, status)>} <br></b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      响应 body，headers，status转换出事函数。会覆盖默认的转换处理函数<br>
                 *  <b>cache {boolean|Cache} <br> </b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      是否缓存。当 cache=true，使用 $http 的缓存；当 cache 为 $cacheFactory 的实例时，用cache进行缓存<br>
                 *  <b>timeout {number|Promise} <br> </b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      请求超时设置。可以是毫秒数，也可以是一个promise，当为promise时，应该在promise得到解决的时候停止请求。<br>
                 *  <b>withCredentials {boolean} <br> </b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      设置这个 withCredentials 标记到 XHR 对象中，withCredentials 标记时候使用跨越cookie。更对关于withCredentials请查看相关文档。<br>
                 *  <b>responseType {string} <br> </b>
                 *  &nbsp;&nbsp;&nbsp;
                 *      返回数据类型。更多信息请查看 requestType。
                 * @returns {Promise} 返回一个承诺，使用angularjs 的 $q 服务产生
                 *
                 * @example post 请求
                 *      ApiUtil.request('http://127.0.0.1:3000/test','POST', {
                 *              aa:'aa'
                 *          },
                 *          "test data",
                 *          {
                 *              headers:{
                 *                  aaa: "aaa",
                 *                  bbb: function() { return "bbb"; },
                 *                  ccc: function{} { return "ccc"; }
                 *              },
                 *              withCredentials: true
                 *          }
                 *      )
                 *
                 * @example get 请求
                 *      ApiUtil.request('http://127.0.0.1:3000/test','GET', {
                 *              aa:'aa'
                 *          },
                 *          "test data",
                 *          {
                 *              headers:{
                 *                  aaa: "aaa",
                 *                  bbb: function() { return "bbb"; },
                 *                  ccc: function{} { return "ccc"; }
                 *              },
                 *              withCredentials: true
                 *          }
                 *      )
                 */
                request: function (url, method, params, data, config) {
                    var deferred = $q.defer();
                    var req_config = config || {};

                    if (!/http[s]?:\/\/.*/.test(url)) {
                        url = URL_PREFIX + url;
                    }
                    params = params || {};

                    // request data
                    $http({
                        method: method,
                        url: url,
                        params: params,
                        data: data,
                        timeout: req_config.timeout
                    }).success(function (data, status, headers, config) {
                        deferred.resolve(data);
                    }).error(function (data, status, headers, config) {
                        deferred.reject(data);
                    });

                    return deferred.promise;
                },
                /**
                 * @description 封装 [request]{@link ng.poler.ApiUtil.request} 的 POST 请求方法
                 * @function post
                 * @memberof ng.poler.ApiUtil
                 * @param {string} url    [参考request.url]{@link ng.poler.ApiUtil.request}
                 * @param {Object} params [参考request.params]{@link ng.poler.ApiUtil.request}
                 * @param {Object} config [参考request.config]{@link ng.poler.ApiUtil.request}
                 * @returns {promise}     [参考 request 返回值]{@link ng.poler.ApiUtil.request}
                 */
                post: function (url, params, config) {
                    return request_method.request(url, 'POST', null, params, config);
                },
                /**
                 * @description 封装 [request]{@link ng.poler.ApiUtil.request} 的 GET 请求方法
                 * @function get
                 * @memberof ng.poler.ApiUtil
                 * @param {string} url    [参考request.url]{@link ng.poler.ApiUtil.request}
                 * @param {Object} params [参考request.params]{@link ng.poler.ApiUtil.request}
                 * @param {Object} config [参考request.config]{@link ng.poler.ApiUtil.request}
                 * @returns {promise}     [参考 request 返回值]{@link ng.poler.ApiUtil.request}
                 */
                get: function (url, params, config) {
                    return request_method.request(url, 'GET', params, null, config);
                },
                /**
                 * @description 封装 [request]{@link ng.poler.ApiUtil.request} 的 DELETE 请求方法
                 * @function del
                 * @memberof ng.poler.ApiUtil
                 * @param {string} url    [参考request.url]{@link ng.poler.ApiUtil.request}
                 * @param {Object} params [参考request.params]{@link ng.poler.ApiUtil.request}
                 * @param {Object} config [参考request.config]{@link ng.poler.ApiUtil.request}
                 * @returns {promise}     [参考 request 返回值]{@link ng.poler.ApiUtil.request}
                 */
                delete: function (url, params, config) {
                    return request_method.request(url, 'DELETE', null, params, config);
                },
                /**
                 * @description 封装 [request]{@link ng.poler.ApiUtil.request} 的 PATCH 请求方法
                 * @function patch
                 * @memberof ng.poler.ApiUtil
                 * @param {string} url    [参考request.url]{@link ng.poler.ApiUtil.request}
                 * @param {Object} params [参考request.params]{@link ng.poler.ApiUtil.request}
                 * @param {Object} config [参考request.config]{@link ng.poler.ApiUtil.request}
                 * @returns {promise}     [参考 request 返回值]{@link ng.poler.ApiUtil.request}
                 */
                patch: function (url, params, config) {
                    return request_method.request(url, 'PATCH', null, params, config);
                },
                /**
                 * @description 封装 [request]{@link ng.poler.ApiUtil.request} 的 PUT 请求方法
                 * @function put
                 * @memberof ng.poler.ApiUtil
                 * @param {string} url    [参考request.url]{@link ng.poler.ApiUtil.request}
                 * @param {Object} params [参考request.params]{@link ng.poler.ApiUtil.request}
                 * @param {Object} config [参考request.config]{@link ng.poler.ApiUtil.request}
                 * @returns {promise}     [参考 request 返回值]{@link ng.poler.ApiUtil.request}
                 */
                put: function (url, params, config) {
                    return request_method.request(url, 'PUT', null, params, config);
                }
            };

            return request_method;
        }])
    ;
})(angular.module('ng.poler'));
