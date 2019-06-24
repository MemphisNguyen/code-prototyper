@php
  /**
     * @param $formattedCompName
     * @param $fieldList
     * @param $requireLang
    */
@endphp
<template>
    <f7-page name="country-form">
        <f7-navbar :title="title" back-link>
            <f7-nav-right>
                <f7-link popup-close v-on:click="$f7router.back()">@{{ translate('Close') }}</f7-link>
            </f7-nav-right>
        </f7-navbar>
        <f7-block>
            <f7-list form>
                <input type="hidden" v-model="iData.id">
@foreach ($fieldList as $field => $type)
                <f7-list-input floating-label type="{{ $type }}" label="{{ ucfirst($field) }}" :value="iData.{{ $field }}"
                               @input="iData.{{ $field }} = $event.target.value"></f7-list-input>
@endforeach
@if ($requireLang)
                <f7-list-item
                    title="Save as"
                    smart-select
                    :smart-select-params="{openIn: 'sheet', closeOnSelect: true}"
                >
                    <select name="save_as" :value="iData.lang" @change="changeInputLang($event)">
                        <template v-for="language in languages">
                            <option
                                :key="language.lang"
                                :value="language.lang"
                                :selected="language.lang == iData.lang"
                            >@{{ language.name }}</option>
                        </template>
                    </select>
                </f7-list-item>
@endif
                <f7-list-item>
                    <f7-button raised fill v-on:click="save">@{{ translate('Save') }}</f7-button>
                </f7-list-item>
                <f7-list-item v-if="(iData.id !== 0)">
                    <f7-button raised fill v-on:click="create">@{{ translate('Save as') }}</f7-button>
                </f7-list-item>
                <f7-list-item>
                    <f7-button raised popup-close v-on:click="$f7router.back()">@{{ translate('Cancel') }}</f7-button>
                </f7-list-item>
            </f7-list>
        </f7-block>
    </f7-page>
</template>
<style>
    .button {
        width: 100%;
    }
</style>

<script>
import { APIRoutes } from "./routes.js";
import translate from "@/js/dictionary";
import Config from "@/js/config.js";
export default {
    name: "{{ $formattedCompName }}Form",
    data() {
        return {
            iData: {
                id: 0,
@foreach ($fieldList as $field => $type)
                {{ $field }}: "",
@endforeach
@if ($requireLang)
                lang: "en"
@endif
            },

            languages: [],
            userLang: 'en',
        };
    },
    computed: {
        title() {
            let lang = this.getUserLang();
            return this.iData.id === 0
                ? translate("Add new item", lang)
                : translate("Edit", lang);
        }
    },
    created() {
        const _this = this;
@if ($requireLang)
            _this.languages = JSON.parse(_this.getAllLangs());
@endif
        _this.userLang = _this.getUserLang();
        _this.$f7.dialog.preloader(_this.translate("Loading"));
        _this.$f7router.once("routeChanged", () => {
            if (!_this.$f7router.currentRoute.params.hasOwnProperty("id")) {
                _this.$f7.dialog.close();
                return;
            }
            _this.iData.id = _this.$f7router.currentRoute.params.id;
            _this.fetchData();
        });
    },
    methods: {
        translate(word) {
            return translate(word, this.userLang);
        },
        save() {
            if (this.iData.id === 0) {
                this.create();
            } else {
                this.update();
            }
        },
        create() {
            const _this = this;
            _this.$f7.dialog.preloader(_this.translate("Adding"));
            let sendData = JSON.parse(JSON.stringify(_this.iData));
            sendData.id = 0;
            _this.$request.promise
                .post(APIRoutes.getApiLink("create"), sendData, "text")
                .then(response => {
                    response = JSON.parse(response);
                    if (response.code === 1) {
                        _this.$f7router.emit("newData");
                        _this.$f7router.back();
                    } else {
                        _this.$f7.dialog.alert(response.msg, Config.errorTitle);
                    }
                })
                .catch(err => {
                    _this.$f7.dialog.alert(_this.errorMsg.default, Config.errorTitle);
                })
                .finally(() => {
                    _this.$f7.dialog.close();
                });
        },
        update() {
            const _this = this;
            _this.$f7.dialog.preloader(_this.translate("Updating"));
            _this.$request.promise
                .post(APIRoutes.getApiLink("update"), _this.iData, "text")
                .then(response => {
                    response = JSON.parse(response);
                    if (response.code === 1) {
                        _this.$f7router.emit("newData");
                        _this.$f7.dialog.close();
                        _this.$f7router.back();
                    } else {
                        _this.$f7.dialog.close();
                        _this.$f7.dialog.alert(response.msg, Config.errorTitle);
                    }
                })
                .catch(err => {
                    _this.$f7.dialog.close();
                    _this.$f7.dialog.alert(_this.errorMsg.default, Config.errorTitle);
                });
        },
        fetchData() {
            const _this = this;
            _this.$f7.dialog.close();

            if (!_this.iData.id) return;

            _this.$request.promise
                .get(APIRoutes.getApiLink("get"), {
@if ($requireLang)
                    lang: _this.iData.lang,
@endif
                    id: _this.iData.id
                })
                .then(response => {
                    response = JSON.parse(response);
                    if (response.code === 1) {
                        @foreach ($fieldList as $field => $type)
                            _this.iData.{{ $field }} = response.data.{{ $field }};
                        @endforeach
                    } else {
                        _this.$f7.dialog.alert(response.msg, Config.errorTitle);
                    }
                })
                .catch(err => {
                    _this.$f7.dialog.alert(_this.errorMsg.default, Config.errorTitle);
                    console.error(err);
                });
        },
        changeInputLang($event) {
            this.iData.lang = $event.target.value;
            this.fetchData();
        }
    }
}
</script>
