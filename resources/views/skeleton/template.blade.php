@php
  /**
    * @param $componentName
    * @param $dynamicData
    * @param $requireLang
    * @param $dynamicMethods
    */
@endphp
<template>
    <f7-page name="{{ $componentName }}"
        infinite
        :infinite-preloader="!allowInfinite && allowInfinite != undefined"
        @infinite="loadMore"
    >
        <f7-navbar title="{{ ucfirst($componentName) }}" back-link="Back">
            <f7-link @click="toggleMultipleSelect" slot="nav-right">
                Select
            </f7-link>
            <f7-subnavbar>
                <f7-segmented raised round>
                    <f7-button round outline :active="filterPopupOpened" @click="filterPopupOpened = true">
                        <f7-icon f7="sort-down"></f7-icon>
                        Sort
                    </f7-button>
                    <f7-button round outline :active="searchPopupOpened" @click="searchPopupOpened = true" >
                        <f7-icon material="search"></f7-icon>
                        Search
                    </f7-button>
                </f7-segmented>
            </f7-subnavbar>
        </f7-navbar>

        <!-- DATA LIST -->
        <f7-block>
            <f7-list :key="forceUpdateKey">
                <f7-list-item  v-for="value in oData" swipeout :checkbox="multipleSelect" name="selectedList"
                    :title="value.{{ $displayField }}" {{ empty($displaySubField) ? '' : ':footer="value.' . $displaySubField . '"' }} v-bind:key="value.id" :value="value.id"
                    v-on:click="swiping == 0 && edit(value.id)"
                    :checked="selectedList.indexOf(value.id) >= 0"
                    @change="toggleSelect"
                    @swipeout:deleted="destroy(value.id)"
                    @swipeout:open="swiping += 1"
                    @swipeout:closed="swiping -= 1">
                    <span slot="subtitle"></span>
                    <f7-swipeout-actions right>
                        <f7-swipeout-button delete text="Delete"
                            :confirm-title="`Deleting ${value.name}?`"
                            :confirm-text="`Do you really want to delete ${value.name}?`"></f7-swipeout-button>

                        <f7-swipeout-button :text="value.active ? 'Inactive' : 'Active'" @click="item.active = !item.active"></f7-swipeout-button>
                    </f7-swipeout-actions>
                </f7-list-item>
            </f7-list>

            <!-- BUTTON TRIGGER ADD POPUP -->
            <f7-button fill round :class="{'add-button': true, 'hide': multipleSelect}" @click="savePopupOpened = true">
                <f7-icon f7="add" color="white"></f7-icon>
            </f7-button>

            <!-- BUTTON TRIGGER ACTIONS -->
            <f7-block class="block-actions">
                <div class="actions"  :class="{ 'show': multipleSelect}">
                    <f7-button fill raised round :color="selectedList.length == 0 ? 'gray' : 'green'">Active</f7-button>
                    <f7-button fill raised round color="gray">Inactive</f7-button>
                    <f7-button fill raised round @click="confirmMultipleDelete" :color="selectedList.length == 0 ? 'gray' : 'red'">
                        Delete
                    </f7-button>
                </div>
            </f7-block>

            <!-- POPUP OF ADD/EDIT  -->
            <f7-popup class="save-popup" :opened="savePopupOpened" @popup:closed="savePopupOpened = false">
                <f7-page>
                    <f7-navbar :title="(iData.id === 0) ? 'Add new' : 'Edit '">
                        <f7-nav-right>
                            <f7-link popup-close v-on:click="resetIData">Close</f7-link>
                        </f7-nav-right>
                    </f7-navbar>
                    <f7-block>
                        <f7-list form>
                            <input type="hidden" v-model="iData.id">
@foreach ($fields as $field => $type)
                            <f7-list-input type="{{ $type }}" label="{{ ucfirst($field) }}" :value="iData.{{ $field }}"
                                           @input="iData.{{ $field }} = $event.target.value"></f7-list-input> {{ $type }}
@endforeach
@if ($requireLang)
                            <f7-list-input type="select" label="Save as" :value="iData.lang" @change="iData.lang = $event.target.value">
                                <option v-for="lang in langs" :key="lang.lang" :value="lang.lang">@{{ lang.name }}</option>
                            </f7-list-input>
@endif
                            <f7-list-item >
                                <f7-button raised fill v-on:click="save">Save</f7-button>
                            </f7-list-item>
                            <f7-list-item v-if="(iData.id !== 0)">
                                <f7-button raised fill v-on:click="create">Save as</f7-button>
                            </f7-list-item>
                            <f7-list-item >
                                <f7-button raised popup-close v-on:click="resetIData">Cancel</f7-button>
                            </f7-list-item>
                        </f7-list>

                    </f7-block>
                </f7-page>
            </f7-popup>

            <!-- POPUP SEARCH -->
            <f7-popup class="search-popup" :opened="searchPopupOpened" @popup:closed="searchPopupOpened = false">
                <f7-page>
                    <f7-navbar title="Search">
                        <f7-nav-right>
                            <f7-link popup-close>Close</f7-link>
                        </f7-nav-right>
                    </f7-navbar>
                    <f7-block class="search-form">
                        <f7-list form>
@foreach ($fields as $field => $type)
                            <f7-list-input type="{{ $type }}" label="{{ ucfirst($field) }}" :value="iData.{{ $field }}"
                                           @input="iData.{{ $field }} = $event.target.value"></f7-list-input>
@endforeach
                            <f7-list-item>
                                <f7-button raised fill v-on:click="search">Search</f7-button>
                            </f7-list-item>
                            <f7-list-item >
                                <f7-button raised popup-close>Cancel</f7-button>
                            </f7-list-item>
                        </f7-list>
                    </f7-block>
                </f7-page>
            </f7-popup>

            <!-- POPUP FILTER -->
            <f7-popup class="filter-popup" :opened="filterPopupOpened" @popup:closed="filterPopupOpened = false">
                <f7-page>
                    <f7-navbar title="Sort and filter">
                        <f7-nav-right>
                            <f7-link popup-close>Close</f7-link>
                        </f7-nav-right>
                    </f7-navbar>
                    <f7-block class="filter-form">
                        <f7-card>
                            <f7-list>
                                <f7-list-input type="select" label="Sort by" :value="sData.sortData.by" @change="sData.sortData.by = $event.target.value">
                                    <option value="">None</option>
@foreach ($fields as $field => $type)
                                    <option value="$field">{{ ucfirst($field) }}</option>
@endforeach
                                </f7-list-input>
                                <f7-list-item>
                                    <f7-list-item-cell>
                                        <f7-radio name="order" value="asc" :checked="sData.sortData.order=='asc'" @change="sData.sortData.order = $event.target.value;"></f7-radio> <label>Ascending</label>
                                        <f7-radio name="order" value="des" :checked="sData.sortData.order=='des'" @change="sData.sortData.order = $event.target.value;"></f7-radio> <label>Descending</label>
                                    </f7-list-item-cell>
                                </f7-list-item>
                            </f7-list>
                        </f7-card>
                        <f7-card title="Filter">
                        </f7-card>
                        <f7-button raised round fill>Apply</f7-button>
                    </f7-block>
                </f7-page>
            </f7-popup>
        </f7-block>
    </f7-page>
</template>

<style>
.page {
    background-color: white;
    overflow: hidden;
}
.search-form .list ul:after {
    content: unset;
}
.popup .button{
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
    width: max-content;
    line-height: 50px;
    margin-left: auto;
    position: sticky;
    bottom: 10px;
    z-index: 2;
    white-space: nowrap;
}
.add-button.hide {
    opacity: 0;
    pointer-events: none;
}
.add-button::after {
    content: "Add new";
    animation: show-name 3s;
    overflow: hidden;
    width: 0;
    color: transparent;
    font-size: 0;
}
@keyframes show-name {
    0% {
        color: white;
        font-size: 14px;
    }
    80% {
        color: white;
    }
    90% {
        color: transparent;
        font-size: 14px;
    }
    100% {
        font-size: 0;
    }

}
</style>

<script>
import { Promise } from 'q';
export default {
    name: "{{ $componentName }}",
    data() {
        return {
            // Static data
            confirmPopupOpened: false,
            savePopupOpened: false,
            searchPopupOpened: false,
            allowInfinite: true,
            filterPopupOpened: false,
            multipleSelect: false,
            forceUpdateKey: 0,
            selectedList: [],
            pageNo: 1,
            pageSize: 20,
            swiping: 0,

            // Dynamic data
            {!! $dynamicData !!}
        }
    },
    created() {
        const _this = this;
        this.$request.setup({
            headers: {
                'api-key': '1234abcd'
            }
        });
        _this.updateViewData();

@includeWhen($requireLang, 'skeleton.js.langRequest')
    },
    methods: {
        // STATIC METHOD
        @include('skeleton.js.sMethod')

        //===============
        // DYNAMIC METHODS
        {!! $dynamicMethods !!}
    }
}
</script>

