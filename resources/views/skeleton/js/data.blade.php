@php
  /**
    * @param $fields
    * @param $requireLang
    * @param $apiURI
    */
@endphp
            oData: [],
            sData: {
@foreach ($fields as $field => $type)
                {{ $field }}: "",
@endforeach
                sortData: {
                    by: "",
                    order: "asc",
                },
                filter: {

                }
            },
            iData: {
                id: 0,
@foreach ($fields as $field => $type)
                {{ $field }}: "",
@endforeach
            },
            apiUrl: {
                prefix: "/{{ $apiURI }}/",
                get: "get",
                list: "list",
                create: "create",
                update: "update",
                destroy: "delete",
            },
@if ($requireLang)
            langs: [],
@endif
