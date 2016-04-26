var MOA;

!function(a) {
    !function(a) {
        a.FormValidate = {
            requiredValid: function(a) {
                var b = a.type && "hidden" === a.type ? !0 : a.hasAttribute("required");
                return b && a.value && $trim(a.value).length;
            },
            selectedWithFake: function(a, b) {
                if ($hasCls(b, "init")) return !1;
                for (var c = !1, d = 0, e = a.options.length; e > d; d++) if (a.options[d].innerHTML === b.innerHTML) {
                    c = !0;
                    break;
                }
                return c;
            },
            patternValid: function(b) {
                var c = b.hasAttribute("pattern") ? new RegExp(b.pattern) : null, d = a.FormValidate.requiredValid(b);
                return c ? d && c.test($trim(b.value)) : d;
            },
            P_MOBILE: "^1[3|4|5|8][0-9]d{8}$",
            P_PHONE: "^[d-s]+$",
            P_EMAIL: "^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$",
            P_NUMBER: "^(?=.*d)d*(?:.d*)?$"
        };
    }(a.form || (a.form = {}));
    a.form;
}(MOA || (MOA = {}));