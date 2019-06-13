@php
    /**
      * @param $fields
      * @param $requireLang
      */
@endphp
        // Handle requests
        fetchData() {
            const _this = this;
            return new Promise((resolve, reject) => {
                let params = {
                    "page_no": _this.pageNo,
                    "page_size": _this.pageSize,
@if ($requireLang)
                    "lang": "en",
@endif
@foreach ($fields as $field => $type)
                    "{{ $field }}": _this.sData.{{ $field }},
@endforeach
                }
                _this.$request.promise.get(_this.getApiLink('list'), params).then((data) => {
                    resolve(JSON.parse(data));
                }).catch((err) => {
                    _this.$f7.dialog.alert(_this.errorMsg.default, _this.errorTitle);
                    reject(err);
                })
            });
        },

        // Handle events
        fillEditForm(iData) {
            this.iData.id = iData.id;
@foreach ($fields as $field => $type)
            this.iData.{{ $field }} = iData.{{ $field }};
@endforeach
        },

        // Helper function
        resetIData() {
            this.iData.id = 0;
@foreach ($fields as $field => $type)
            this.iData.{{ $field }} = '';
@endforeach
@if ($requireLang)
            this.iData.lang = 'en'
@endif
        },
