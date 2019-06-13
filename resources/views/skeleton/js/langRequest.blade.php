        // Get language list
        _this.$request.promise.get(_this.API.host + '/public/language/list'
        ).then((response) => {
            response = JSON.parse(response);
            if (response.code === 1 ) {
                _this.langs = response.data;
            } else {
                _this.$f7.dialog.alert(response.msg, _this.errorTitle);
            }
        }).catch((err) => {
            _this.$f7.dialog.alert(_this.errorMsg.default, _this.errorTitle);
        })
