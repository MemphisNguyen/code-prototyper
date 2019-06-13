        // Handle requests
        create() {
            const _this = this;
            _this.iData.id = 0;
            _this.$f7.dialog.preloader('Adding');
            _this.$request.promise.post(_this.getApiLink('create'), _this.iData, 'text'
            ).then( response => {
                response = JSON.parse(response);
                if (response.code === 1) {
                    _this.updateViewData();
                } else {
                    _this.$f7.dialog.alert(response.msg, _this.errorTitle);
                }
                _this.savePopupOpened = false;
            }).catch((err) => {
                _this.$f7.dialog.alert(_this.errorMsg.default, _this.errorTitle);
            }).finally(() => { _this.$f7.dialog.close(); })
        },
        destroy(id) {
            const _this = this;
            _this.$f7.dialog.preloader('Deleting');
            _this.$request.promise.post(_this.getApiLink('destroy'), {
                'id': id
            }, 'text').then((response) => {
                response = JSON.parse(response);
                if (response.code === 1) {
                    _this.updateViewData();
                    if (_this.multipleSelect) {
                        _this.toggleMultipleSelect();
                    }

                } else {
                    _this.$f7.dialog.alert(response.msg, _this.errorTitle);
                }
                _this.forceUpdateKey += 1;
            }).catch((err) => {
                _this.forceUpdateKey += 1;
                _this.$f7.dialog.alert(_this.errorMsg.default, _this.errorTitle);
            }).finally(() => {
                _this.$f7.dialog.close();
            })
        },
        update() {
            const _this = this;
            _this.$f7.dialog.preloader('Updating');
            _this.$request.promise.post(_this.getApiLink('update'), _this.iData, 'text'
            ).then( response => {
                response = JSON.parse(response);
                if (response.code === 1) {
                    let updatingItem = _this.oData.filter((c) => c.id == _this.iData.id)[0];
                    updatingItem.code = _this.iData.code;
                    updatingItem.name = _this.iData.name;
                    _this.savePopupOpened = false;
                    _this.resetIData();
                    _this.savePopupOpened = false;
                } else {
                    _this.$f7.dialog.alert(response.msg, _this.errorTitle);
                    _this.savePopupOpened = true;
                }
            }).catch( err => {
                _this.$f7.dialog.alert(_this.errorMsg.default, _this.errorTitle);
                _this.savePopupOpened = true;
            }).finally(() => {
                _this.$f7.dialog.close();
            })
        },
        loadMore() {
            const _this = this;
            if (!_this.allowInfinite) return;
            _this.allowInfinite = false;
            _this.pageNo += 1;
            _this.fetchData().then((response) => {
                if (response.code === 1) {
                    _this.oData.push(...response.data);
                    if (_this.oData.length === response.size) {
                        _this.allowInfinite = undefined;
                    } else {
                        _this.allowInfinite = true;
                    }
                } else {
                    _this.pageNo -= 1;
                    _this.$f7.dialog.alert(response.msg, _this.errorTitle);
                }
            }).catch(() => {
                _this.pageNo -= 1;
                _this.$f7.dialog.alert(_this.errorMsg.default, _this.errorTitle);
            })
        },

        // Handle events
        updateViewData() {
            const _this = this;
            _this.pageNo = 1;
            _this.allowInfinite = true;
            _this.$f7.dialog.preloader("Updating data");
            _this.fetchData().then((response) => {
                if (response.code == 1) {
                    _this.oData = response.data;
                    if (response.size <= _this.pageSize)
                        _this.allowInfinite = undefined;
                } else {
                    _this.$f7.dialog.alert(response.msg, _this.errorTitle);
                }
            }).finally(() => {
                _this.$f7.dialog.close();
            });
        },
        search() {
            const _this = this;
            _this.searchPopupOpened = false;
            _this.updateViewData();
        },
        edit(id) {
            const _this = this;
            let focusItem = _this.oData.filter(c => c.id == id)[0];
            _this.fillEditForm(focusItem);
            _this.savePopupOpened = true;
        },
        save() {
            if (this.iData.id === 0) {
                this.create();
            } else {
                this.update();
            }
        },
        toggleSelect($event) {
            const _this = this;
            const value = $event.target.value;

            if ($event.target.checked) {
                _this.selectedList.push(value);
            } else {
                _this.selectedList.splice(_this.selectedList.indexOf(value), 1);
            }
        },
        toggleMultipleSelect() {
            this.multipleSelect = !this.multipleSelect;
            if (!this.multipleSelect) {
                this.selectedList = [];
            }
        },
        confirmMultipleDelete() {
            const _this = this;
            this.$f7.dialog.confirm('Are you sure want to delete these items?', 'Deleting', function () {
                _this.destroy(_this.selectedList)
            })
        },

        // Helper function
        getApiLink(type) {
            if (this.apiUrl.hasOwnProperty(type)) {
                return this.API.host + this.apiUrl.prefix + this.apiUrl[type];
            }
            console.error(`URL of "${type}" type is not defined.`);
        },
