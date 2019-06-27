@php
  /**
     * @param $containFolder
     * @param $formattedCompName
     * @param $fieldList
     * @param $requireLang
     * @param $parentIdField
    */
@endphp
<template>
  <f7-page name="{{ $formattedCompName }}-form">
    <f7-navbar :title="title" back-link>
      <f7-nav-right>
        <f7-link popup-close v-on:click="$f7router.back()">@{{ translate('Close') }}</f7-link>
      </f7-nav-right>
    </f7-navbar>
    <f7-block>
      <f7-list form>
        <input type="hidden" name="id" v-model="iData.id">
@if ($parentIdField)
        <input type="hidden" name="{{ $parentIdField }}" v-model="iData.{{ $parentIdField }}">
@endif
@foreach ($fieldList as $field => $type)
  @if ($parentIdField && $field == $parentIdField)
    @continue
  @endif
        <f7-list-input {{ $type != "datetime-local" ? "floating-label" : "" }} type="{{ $type }}" label="{{ ucfirst($field) }}" :value="iData.{{ $field }}"
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
              >@{{ language.name }}
              </option>
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
  import {APIRoutes} from "./routes.js";
  import translate from "@/js/dictionary";
  import Config from "@/js/config.js";

  export default {
    name: "{{ $formattedCompName }}Form",
    data() {
      return {
        iData: {
          id: undefined,
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
@if ($requireLang)
      this.languages = JSON.parse(this.getAllLangs());
@endif
      this.userLang = this.getUserLang();
      this.$f7router.on("routeChange", this.validateRoute);
    },
    destroyed() {
      this.$f7router.off("routeChange", this.validateRoute);
    },
    methods: {
      translate(word) {
        return translate(word, this.userLang);
      },
      validateRoute(newRoute) {
        if (
          (newRoute.name !== "{{ $containFolder }}.{{ $formattedCompName }}.edit" &&
            newRoute.name !== "{{ $containFolder }}.{{ $formattedCompName }}.create") ||
          this.iData.id !== undefined
        ) return;
        if (
@if ($parentIdField)
          (newRoute.name === "{{ $containFolder }}.{{ $formattedCompName }}.edit" &&
            !newRoute.params.hasOwnProperty("id")) ||
          !newRoute.params.hasOwnProperty("{{ $parentIdField }}")
@else
          newRoute.name === "{{ $containFolder }}.{{ $formattedCompName }}.edit" &&
            !newRoute.params.hasOwnProperty("id")
@endif
        ) {
          console.error("Missing argument.", newRoute.params);
          return;
        }

        this.$f7.dialog.preloader(this.translate("Loading"));
@if ($parentIdField)
        this.iData.{{ $parentIdField }} = newRoute.params.{{ $parentIdField }};
@endif
        if (newRoute.params.hasOwnProperty("id"))
          this.iData.id = newRoute.params.id;
        else this.iData.id = 0;

        this.fetchData();
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
            _this.$f7.dialog.close();
            response = JSON.parse(response);

            _this.handleResponse(response, response => {
                _this.$f7router.emit("newData");
                _this.$f7router.back();
            })
          })
          .catch(err => {
            _this.$f7.dialog.close();
            _this.$f7.dialog.alert(Config.errorMsg.default, Config.errorTitle);
            console.error(err);
          });
      },
      update() {
        const _this = this;
        _this.$f7.dialog.preloader(_this.translate("Updating"));
        _this.$request.promise
          .post(APIRoutes.getApiLink("update"), _this.iData, "text")
          .then(response => {
            _this.$f7.dialog.close();
            response = JSON.parse(response);

            _this.handleResponse(response, response => {
                _this.$f7router.emit("newData");
                _this.$f7router.back();
            })
          })
          .catch(err => {
            _this.$f7.dialog.close();
            _this.$f7.dialog.alert(Config.errorMsg.default, Config.errorTitle);
            console.err(err);
          });
      },
      fetchData() {
        const _this = this;

        if (!_this.iData.id) {
          _this.$f7.dialog.close();
          return;
        }

        _this.$request.promise
          .get(APIRoutes.getApiLink("get"), {
@if ($requireLang)
            lang: _this.iData.lang,
@endif
@if ($parentIdField)
            {{ $parentIdField }}: _this.iData.{{ $parentIdField }},
@endif
            id: _this.iData.id
          })
          .then(response => {
            _this.$f7.dialog.close();
            response = JSON.parse(response);

            _this.handleResponse(response, response => {
@foreach ($fieldList as $field => $type)
                _this.iData.{{ $field }} = response.data.{{ $field }};
@endforeach
            })
          })
          .catch(err => {
            _this.$f7.dialog.close();
            _this.$f7.dialog.alert(Config.errorMsg.default, Config.errorTitle);
            console.error(err);
          });
      },
@if ($requireLang)
      changeInputLang($event) {
        this.iData.lang = $event.target.value;
        this.fetchData();
      }
@endif
    }
  }
</script>
