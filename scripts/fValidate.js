var fValidator = new Class({
    options: {
        msgContainerTag: "div",
        msgClass: "fValidator-msg",
        styleNeutral: {
            "background-color": "#ffc",
            "border-color": "#cc0"
        },
        styleInvalid: {
            "background-color": "#fcc",
            "border-color": "#c00"
        },
        styleValid: {
            "background-color": "#cfc",
            "border-color": "#0c0"
        },
        required: {
            type: "required",
            re: /[^.*]/,
            msg: "This field is required."
        },
        alpha: {
            type: "alpha",
            re: /^[a-z._-]+$/i,
            msg: "This field accepts alphabetic characters only."
        },
        alphanum: {
            type: "alphanum",
            re: /^[a-z0-9._-]+$/i,
            msg: "This field accepts alphanumeric characters only."
        },
        integer: {
            type: "integer",
            re: /^[-+]?\d+$/,
            msg: "Please enter a valid integer."
        },
        real: {
            type: "real",
            re: /^[-+]?\d*\.?\d+$/,
            msg: "Please enter a valid number."
        },
        date: {
            type: "date",
            re: /^((((0[13578])|([13578])|(1[02]))[\/](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[\/](([1-9])|([0-2][0-9])|(30)))|((2|02)[\/](([1-9])|([0-2][0-9]))))[\/]\d{4}$|^\d{4}$/,
            msg: "Please enter a valid date (mm/dd/yyyy)."
        },
        email: {
            type: "email",
            re: /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i,
            msg: "Please enter a valid email."
        },
        phone: {
            type: "phone",
            re: /^[\d\s().-]+$/,
            msg: "Please enter a valid phone."
        },
        url: {
            type: "url",
            re: /^(http|https|ftp)\:\/\/[a-z0-9\-\.]+\.[a-z]{2,3}(:[a-z0-9]*)?\/?([a-z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/i,
            msg: "Please enter a valid url."
        },
        confirm: {
            type: "confirm",
            msg: "Confirm Password does not match original Password."
        },
        onValid: Class.empty,
        onInvalid: Class.empty
    },
    initialize: function(_1, _2) {
        this.form = $(_1);
        this.setOptions(_2);
        this.fields = this.form.getElements("*[class^=fValidate]");
        this.validations = [];
        this.fields.each(function(_3) {
            if (!this._isChildType(_3)) {
                _3.setStyles(this.options.styleNeutral)
            }
            _3.cbErr = 0;
            var _4 = _3.getProperty("class").split(" ");
            _4.each(function(_5) {
                if (_5.match(/^fValidate(\[.+\])$/)) {
                    var _6 = eval(_5.match(/^fValidate(\[.+\])$/)[1]);
                    for (var i = 0; i < _6.length; i++) {
                        if (this.options[_6[i]]) {
                            this.register(_3, this.options[_6[i]])
                        }
                        if (_6[i].charAt(0) == "=") {
                            this.register(_3, $extend(this.options.confirm, {
                                idField: _6[i].substr(1)
                            }))
                        }
                    }
                }
            }.bind(this))
        }.bind(this));
        this.form.addEvents({
            "submit": this._onSubmit.bind(this),
            "reset": this._onReset.bind(this)
        })
    },
    register: function(_8, _9) {
        _8 = $(_8);
        this.validations.push([_8, _9]);
        _8.addEvent("blur", function() {
            this._validate(_8, _9)
        }.bind(this))
    },
    _isChildType: function(el) {
        var _b = el.type.toLowerCase();
        if ((_b == "radio") || (_b == "checkbox")) {
            return true
        }
        return false
    },
    _validate: function(_c, _d) {
        switch (_d.type) {
        case "confirm":
            if ($(_d.idField).getValue() == _c.getValue()) {
                this._msgRemove(_c, _d)
            } else {
                this._msgInject(_c, _d)
            }
            break;
        default:
            if (_d.re.test(_c.getValue())) {
                this._msgRemove(_c, _d)
            } else {
                this._msgInject(_c, _d)
            }
        }
    },
    _validateChild: function(_e, _f) {
        var _10 = this.form[_e.getProperty("name")];
        var _11 = 0;
        var _12 = true;
        for (var i = 0; i < _10.length; i++) {
            if (_10[i].checked) {
                _11++;
                if (!_f.re.test(_10[i].getValue())) {
                    _12 = false;
                    break
                }
            }
        }
        if (_11 == 0 && _f.type == "required") {
            _12 = false
        }
        if (_12) {
            this._msgRemove(_e, _f)
        } else {
            this._msgInject(_e, _f)
        }
    },
    _msgInject: function(_14, _15) {
        if (!$(_14.getProperty("id") + _15.type + "_msg")) {
            var _16 = new Element(this.options.msgContainerTag, {
                "id": _14.getProperty("id") + _15.type + "_msg",
                "class": this.options.msgClass
            }).setHTML(_15.msg).setStyle("opacity", 0).injectAfter(_14).effect("opacity", {
                duration: 500,
                transition: Fx.Transitions.linear
            }).start(0, 1);
            _14.cbErr++;
            this._chkStatus(_14, _15)
        }
    },
    _msgRemove: function(_17, _18, _19) {
        _19 = _19 || false;
        if ($(_17.getProperty("id") + _18.type + "_msg")) {
            var el = $(_17.getProperty("id") + _18.type + "_msg");
            el.effect("opacity", {
                duration: 500,
                transition: Fx.Transitions.linear,
                onComplete: function() {
                    el.remove()
                }
            }).start(1, 0);
            if (!_19) {
                _17.cbErr--;
                this._chkStatus(_17, _18)
            }
        }
    },
    _chkStatus: function(_1b, _1c) {
        if (_1b.cbErr == 0) {
            _1b.effects({
                duration: 500,
                transition: Fx.Transitions.linear
            }).start(this.options.styleValid);
            this.fireEvent("onValid", [_1b, _1c], 50)
        } else {
            _1b.effects({
                duration: 500,
                transition: Fx.Transitions.linear
            }).start(this.options.styleInvalid);
            this.fireEvent("onInvalid", [_1b, _1c], 50)
        }
    },
    _onSubmit: function(_1d) {
        _1d = new Event(_1d);
        var _1e = true;
        this.validations.each(function(_1f) {
            if (this._isChildType(_1f[0])) {
                this._validateChild(_1f[0], _1f[1])
            } else {
                this._validate(_1f[0], _1f[1])
            }
            if (_1f[0].cbErr > 0) {
                _1e = false
            }
        }.bind(this));
        if (!_1e) {
            _1d.stop()
        }
        return _1e
    },
    _onReset: function() {
        this.validations.each(function(_20) {
            if (!this._isChildType(_20[0])) {
                _20[0].setStyles(this.options.styleNeutral)
            }
            _20[0].cbErr = 0;
            this._msgRemove(_20[0], _20[1], true)
        }.bind(this))
    }
});
fValidator.implement(new Events);
fValidator.implement(new Options);