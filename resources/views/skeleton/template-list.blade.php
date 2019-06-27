@php
  /**
     * @param $componentName
     * @param $formattedCompName
     * @param $containFolder
     * @param $fieldList
     * @param $displayField
     * @param $requireLang
     * @param $displaySubField
     * @param $parentIdField
    */
@endphp
<template>
  <f7-page
    name="{{ $componentName }}"
    ref="infinitePage"
    infinite
    :infinite-preloader="false"
    @infinite="loadMore"
  >
    <f7-navbar :title="title" :back-link="!multipleSelect">
      <f7-link
        @click="toggleMultipleSelect"
        slot="nav-right"
      >@{{ multipleSelect ? translate('Cancel') : translate('Select') }}
      </f7-link>
      <f7-subnavbar>
        <f7-segmented raised round>
          <f7-button round outline :active="filterPopupOpened" @click="filterPopupOpened = true">
            <f7-icon f7="filter"></f7-icon>
            @{{ translate('Sort') }}
          </f7-button>
          <f7-button round outline :active="searchPopupOpened" @click="searchPopupOpened = true">
            <f7-icon material="search"></f7-icon>
            @{{ translate('Search') }}
          </f7-button>
        </f7-segmented>
      </f7-subnavbar>
    </f7-navbar>

    <!-- DATA LIST -->
    <f7-block>
      <f7-list :key="'list-' + forceUpdateKey">
        <f7-list-item
          v-for="value in oData"
          v-bind:key="value.id"
          :value="value.id"
          name="selectedList"
          :title="value.{{ $displayField }}"
@if (isset($displaySubField) && !empty($displaySubField))
          :footer="value.{{ $displaySubField }}"
@endif
          :link="(!multipleSelect && $store.state.perms.includes('{{ $containFolder }}.{{ $formattedCompName }}.edit')) ?
            getPath('{{ $containFolder }}.{{ $formattedCompName }}.edit', {id: value.id{{ $parentIdField ? ", $parentIdField: sData.$parentIdField" : "" }} }) :
            false"
          :checkbox="multipleSelect"
          :checked="selectedList.indexOf(value.id) >= 0"
          @change="toggleSelect"
          swipeout
          @swipeout:deleted="destroy(value.id)"
          @swipeout:open="swiping += 1"
          @swipeout:closed="swiping -= 1"
        >
          <f7-swipeout-actions right>
            <f7-swipeout-button
              delete
              :text="translate('Delete')"
              :confirm-title="`Deleting ${value.{{ $displayField }}}?`"
              :confirm-text="`Do you really want to delete ${value.{{ $displayField }}}?`"
            ></f7-swipeout-button>

            <f7-swipeout-button
              :text="value.active ? translate('Inactive') : translate('Active')"
              @click="item.active = !item.active"
            ></f7-swipeout-button>
          </f7-swipeout-actions>
        </f7-list-item>
      </f7-list>
      <f7-block v-if="allowInfinite != undefined">
        <f7-row>
          <f7-col class="preloader-container">
            <f7-preloader :size="42"></f7-preloader>
          </f7-col>
        </f7-row>
      </f7-block>

      <!-- BUTTON LINK TO ADD PAGE -->
      <f7-button
        v-if="$store.state.perms.includes('{{ $containFolder }}.{{ $formattedCompName }}.create')"
        fill
        round
        :class="{'add-button': true, 'hide': multipleSelect}"
        :href="getPath('{{ $containFolder }}.{{ $formattedCompName }}.create'{{ $parentIdField ? ", { $parentIdField: sData.$parentIdField}" : '' }})"
      >
        <f7-icon f7="add" color="white"></f7-icon>
        @{{ translate('New') }} &nbsp;&nbsp;
      </f7-button>

      <!-- BUTTON TRIGGER ACTIONS -->
      <f7-block class="block-actions">
        <div class="actions" :class="{ 'show': multipleSelect}">
          <f7-button fill raised round :color="selectedList.length == 0 ? 'gray' : 'green'">@{{ translate('Active') }}
          </f7-button>
          <f7-button fill raised round color="gray">@{{ translate('Inactive') }}</f7-button>
          <f7-button fill raised round @click="confirmMultipleDelete"
                     :color="selectedList.length == 0 ? 'gray' : 'red'">
            @{{ translate('Delete') }}
          </f7-button>
        </div>
      </f7-block>

      <!-- POPUP SEARCH -->
      <f7-popup
        class="search-popup"
        :opened="searchPopupOpened"
        @popup:closed="searchPopupOpened = false">
        <f7-view>
          <f7-page>
            <f7-navbar :title="translate('Search')">
              <f7-nav-right>
                <f7-link popup-close>@{{ translate('Close') }}</f7-link>
              </f7-nav-right>
            </f7-navbar>
            <f7-block class="search-form">
              <f7-list form>
@foreach ($fieldList as $field => $type)
  @if ($field == $parentIdField)
                <input
                  type="hidden"
                  name="{{ $parentIdField }}"
                  :value="sData.{{ $parentIdField }}"
                  @input="sData.{{ $parentIdField }} = $event.target.value"
                >
    @continue
  @endif
                <f7-list-input {{ $type != "datetime-local" ? "floating-label" : "" }} type="{{ $type }}" label="{{ ucfirst($field) }}" :value="sData.{{ $field }}"
                               @input="sData.{{ $field }} = $event.target.value"></f7-list-input>
@endforeach
@if ($requireLang)
                <f7-list-item
                  title="Language"
                  smart-select
                  :smart-select-params="{openIn: 'sheet', closeOnSelect: true}"
                >
                  <select
                    name="language"
                    :value="sData.lang"
                    @change="sData.lang = $event.target.value"
                  >
                    <template v-for="language in languages">
                      <option
                        :key="language.lang"
                        :value="language.lang"
                        :selected="language.lang == sData.lang"
                      >@{{ language.name }}
                      </option>
                    </template>
                  </select>
                </f7-list-item>
@endif
                <f7-list-item>
                  <f7-button raised fill v-on:click="search">@{{ translate('Search') }}</f7-button>
                </f7-list-item>
                <f7-list-item>
                  <f7-button
                    raised
                    popup-close
                    v-on:click="resetSearch"
                  >@{{ translate('Clear search result') }}
                  </f7-button>
                </f7-list-item>
                <f7-list-item>
                  <f7-button raised popup-close>@{{ translate('Cancel') }}</f7-button>
                </f7-list-item>
              </f7-list>
            </f7-block>
          </f7-page>
        </f7-view>
      </f7-popup>

      <!-- POPUP SORT -->
      <f7-popup
        class="filter-popup"
        :opened="filterPopupOpened"
        @popup:closed="filterPopupOpened = false"
      >
        <f7-view>
          <f7-page>
            <f7-navbar :title="translate('Sort')">
              <f7-nav-right>
                <f7-link popup-close>@{{ translate('Close') }}</f7-link>
              </f7-nav-right>
            </f7-navbar>
            <f7-block class="filter-form">
              <f7-card>
                <f7-list form>
                  <f7-list-item
                    title="Sort by"
                    smart-select
                    :smart-select-params="{openIn: 'sheet', closeOnSelect: true}"
                  >
                    <select
                      name="sort_by"
                      :value="sData.sortData.by"
                      @change="sData.sortData.by = $event.target.value"
                    >
@foreach ($fieldList as $field => $type)
  @if ($field == $parentIdField)
    @continue
  @endif
                      <option value="{{ $field }}" {{ ($field == $displayField) ? "selected" : "" }}>{{ ucfirst($field) }}</option>
@endforeach
                    </select>
                  </f7-list-item>
                  <f7-list-item
                    smart-select
                    :smart-select-params="{openIn: 'sheet', closeOnSelect: true}"
                    title="Order"
                  >
                    <select
                      name="sort_type"
                      :value="sData.sortData.type"
                      @change="sData.sortData.type = $event.target.value"
                    >
                      <option value="asc" selected>@{{ translate('Ascending') }}</option>
                      <option value="desc">@{{ translate('Descending') }}</option>
                    </select>
                  </f7-list-item>
                </f7-list>
              </f7-card>
              <f7-button
                raised
                fill
                @click="filterPopupOpened = false; updateViewData()"
              >@{{ translate('Apply') }}
              </f7-button>
            </f7-block>
          </f7-page>
        </f7-view>
      </f7-popup>
    </f7-block>
  </f7-page>
</template>

<style scoped>
  .search-form .list ul:after {
    content: unset;
  }

  .popup .button {
    width: 100%;
  }

  .block-action {
    z-index: 3;
  }

  .actions {
    display: flex;
    position: fixed;
    bottom: 10px;
    left: -101%;
    width: 100%;
    transition: left 0.3s;
  }

  .actions.show {
    left: 0;
  }

  .actions .button {
    flex-grow: 1;
    margin-left: 10px;
    padding: 10px 0;
    height: auto;
    text-transform: uppercase;
    font-weight: bold;
  }

  .actions .button:last-child {
    margin-right: 10px;
  }

  .add-button {
    height: 50px;
    width: auto;
    line-height: 50px;
    margin-left: auto;
    position: fixed;
    bottom: 10px;
    right: 20px;
    z-index: 2;
    white-space: nowrap;
  }

  .add-button.hide {
    opacity: 0;
    pointer-events: none;
  }

  .infinite-scroll-content > .block {
    margin-bottom: 75px;
  }

  .md .infinite-scroll-preloader {
    margin-top: 10px;
    margin-bottom: 10px;
  }

  .preloader-container {
    text-align: center;
  }
</style>

<script>
  import {Promise} from "q";
  import {APIRoutes} from "./routes.js";
  import Config from "@/js/config.js";
  import translate from "@/js/dictionary.js";

  export default {
    name: "{{ $formattedCompName }}Page",
    data() {
      return {
        // Static data
        allowInfinite: true,
        multipleSelect: false,
        searchPopupOpened: false,
        filterPopupOpened: false,
        forceUpdateKey: 0,
        selectedList: [],
        pageNo: 1,
        pageSize: 20,
        swiping: 0,

        oData: [],
        // Dynamic data
        sData: {
@foreach ($fieldList as $field => $type)
          {{ $field }}: "",
@endforeach
@if ($requireLang)
          lang: "en",
@endif
          sortData: {
            by: "{{ $displayField }}",
            type: "asc",
          },
        },
        userLang: 'en',
@if ($requireLang)
          languages: []
@endif
      }
    },
    computed: {
      title() {
        if (!this.multipleSelect) return this.translate("{{ $componentName }}");
        let length = this.selectedList.length;
        if (length > 0)
          return `${length} item${length == 1 ? '' : 's'} selected`;
        return 'Select items';
      }
    },
    created() {
      this.userLang = this.getUserLang();
@if ($requireLang)
      this.languages = JSON.parse(this.getAllLangs());
@endif
@if ($parentIdField)
      this.$f7router.on("routeChange", this.validateRoute);
@else
      this.updateViewData();
@endif
      this.$f7router.on("newData", this.updateViewData);
    },
    destroyed() {
      this.$f7router.off("newData", this.updateViewData);
@if ($parentIdField)
      this.$f7router.off("routeChange", this.validateRoute);
@endif
    },
    methods: {
      // STATIC METHOD
      // Helper
      translate(word) {
        return translate(word, this.userLang);
      },
@if ($parentIdField)
      validateRoute(newRoute) {
        if (
          newRoute.name != "{{ $containFolder }}.{{ $formattedCompName }}.index" ||
          this.sData.{{ $parentIdField }} ||
          !newRoute.params.hasOwnProperty("{{ $parentIdField }}")
        ) {
          return;
        }
        this.sData.{{ $parentIdField }} = newRoute.params.{{ $parentIdField }};
        this.updateViewData();
      },
@endif
      // Handle requests
      destroy(id) {
        const _this = this;
        _this.$f7.dialog.preloader(_this.translate("Deleting"));
        _this.$request.promise
          .post(
            APIRoutes.getApiLink("destroy"),
            {
              id: id
            },
            "text"
          )
          .then(response => {
            _this.$f7.dialog.close();
            response = JSON.parse(response);

            _this.handleResponse(response, response => {
              _this.updateViewData();
              if (_this.multipleSelect) {
                _this.toggleMultipleSelect();
              }
            });
            _this.forceUpdateKey += 1;
          })
          .catch(err => {
            _this.$f7.dialog.close();
            _this.forceUpdateKey += 1;
            _this.$f7.dialog.alert(Config.errorMsg.default, Config.errorTitle);
            console.error(err)
          })
      },
      loadMore() {
        const _this = this;
        if (!_this.allowInfinite) return;

        _this.allowInfinite = false;
        _this.pageNo += 1;
        _this
          .fetchData()
          .then(response => {
            _this.handleResponse(response, response => {
              _this.oData.push(...response.data);
              if (_this.oData.length === response.size) {
                _this.allowInfinite = undefined;
              } else {
                _this.allowInfinite = true;
              }
            });
          })
          .catch(err => {
            _this.pageNo -= 1;
            _this.$f7.dialog.alert(Config.errorMsg.default, Config.errorTitle);
            console.error(err);
          });
      },

      // Handle events
      updateViewData() {
        const _this = this;
        _this.pageNo = 1;
        _this.allowInfinite = true;
        _this.$f7.dialog.preloader(_this.translate("Updating data"));
        _this
          .fetchData()
          .then(response => {
            _this.$f7.dialog.close();

            _this.handleResponse(response, response => {
              _this.oData = response.data;
              if (response.size <= _this.pageSize)
                _this.allowInfinite = undefined;
            });
          })
          .catch(err => {
            _this.$f7.dialog.close();
            _this.$f7.dialog.alert(Config.errorMsg.default, Config.errorTitle);
            console.error(err);
          });
      },
      search() {
        this.searchPopupOpened = false;
        this.$refs.infinitePage.$children[1].$el.scrollTop = 0;
        this.updateViewData();
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
          this.forceUpdateKey += 1;
        }
      },
      confirmMultipleDelete() {
        const _this = this;
        this.$f7.dialog.confirm(
          "Are you sure want to delete these items?",
          "Deleting",
          function () {
            _this.destroy(_this.selectedList);
          }
        );
      },

      //===============
      // DYNAMIC METHODS

      resetSearch() {
@foreach ($fieldList as $field => $type)
        this.sData.{{ $field }} = "";
@endforeach
@if ($requireLang)
        this.sData.lang = "en";
@endif
        this.search();
      },
      fetchData() {
        const _this = this;
        return new Promise((resolve, reject) => {
          let params = {
            page_no: _this.pageNo,
            page_size: _this.pageSize,
@if ($requireLang)
            lang: _this.sData.lang,
@endif
@foreach ($fieldList as $field => $type)
            {{ $field }}: _this.sData.{{ $field }},
@endforeach
            order_by: _this.sData.sortData.by,
            order_type: _this.sData.sortData.type
          };

          _this.$request.promise
            .get(APIRoutes.getApiLink("list"), params)
            .then(data => {
              resolve(JSON.parse(data));
            })
            .catch(err => {
              reject(err);
            });
        });
      }
    }
  }
</script>

