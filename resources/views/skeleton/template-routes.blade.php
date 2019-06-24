@php
    /**
       * @param $componentName
       * @param $formattedCompName
       * @param $containFolder
      */
@endphp
import {{ $formattedCompName }}Page from './{{ $formattedCompName }}List.vue';
import {{ $formattedCompName }}Form from './{{ $formattedCompName }}Form.vue';
import Config from '@/js/config.js';

const prefix = '/{{ $containFolder }}/{{ $formattedCompName }}';

let apiUrl = {
    prefix: "/public/{{ strtolower(str_replace(' ', '_', $componentName)) }}/",
    get: "get",
    list: "list",
    create: "create",
    update: "update",
    destroy: "delete",
};
let routes = [
    {
        path: '/',
        component: {{$formattedCompName}}Page,
        name: '{{ $containFolder }}.{{ $formattedCompName }}.index'
    },
    {
        path: '/create/',
        component: {{ $formattedCompName }}Form,
        name: '{{ $containFolder }}.{{ $formattedCompName }}.create'
    },
    {
        path: '/edit/:id/',
        component: {{ $formattedCompName }}Form,
        name: '{{ $containFolder }}.{{ $formattedCompName }}.edit'
    },
];
routes = routes.map((r) => {
    r.path = prefix + r.path;
    return r;
});

export default routes;
export var APIRoutes = {
    routes: apiUrl,
    getApiLink(type) {
        if (apiUrl.hasOwnProperty(type)) {
            return Config.API.host + apiUrl.prefix + apiUrl[type];
        }
        console.error(`URL of "${type}" type is not defined.`);
    },
};
