<?php

namespace App\Http\Controllers;

use App\Table;
use App\Validator\PrototypeValidator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class PrototypeController
 * @package App\Http\Controllers
 */
class PrototypeController extends Controller
{
    /**
     * Display form for generation
     */
    public function index() {
        return view('index');
    }

    public function test() {
        $column = Table::getColumns('mt_place');
        $column[] = Table::getColumns('mt_place_name');
        return $column;
    }

    public function handleSubmit(Request $request) {
        $iData = $request->validate([
            'name' => 'required|string',
            'sub_folder' => 'string',
            'table' => 'required|string',
            'mul_lang' => 'nullable|boolean',
            'sub_table' => 'required_with:mul_lang',
            'api_uri' => 'required|string',
            'display_field' => 'required|string',
            'sub_field' => 'nullable|string',
        ]);
        $iData['name'] = str_replace(' ', '', strtolower($iData['name']));
        $iData['sub_folder'] = ucfirst(str_replace(' ', '', strtolower($iData['sub_folder'])));
        $columns = Table::getColumns($iData['table'])->toArray();
        if (isset($iData['mul_lang'])) {
            $columns = array_merge($columns, Table::getColumns($iData['sub_table'])->toArray());
        }
        $fields = array();

        foreach ($columns as $col) {
            $fields[$col->COLUMN_NAME] = $col->DATA_TYPE;
        }

        $folderName = $this->__prototyping($iData['name'], $iData['sub_folder'], $fields, $iData['api_uri'], $iData['display_field'],
            isset($iData['mul_lang']));

        return view('generated' , [
            'folderName' => $folderName
        ]);
    }

    /**
     * @param $componentName
     * @param $subFolder
     * @param $fieldList
     * @param $apiURI
     * @param $displayField
     * @param $requireLang
     * @param string $displaySubField
     * @throws \ReflectionException
     */
    private function __prototyping($componentName, $subFolder, $fieldList, $apiURI, $displayField, $requireLang,
                                   $displaySubField = '') {
        foreach ($fieldList as $field => $type) {
            $fieldList[$field] = $this->__dataTypeToInput($type);
        }
        $dynamicData = $this->__generateDataSection($fieldList, $apiURI, $requireLang);
        $dynamicMethods = $this->__generateMethodSection($fieldList, $requireLang);
        $fileContent = $this->__generateFiles($componentName, $fieldList, $dynamicData, $dynamicMethods, $displayField,
            $requireLang, $displaySubField);
        return $this->__storeFile($subFolder, $componentName, $fileContent);
}

    /**
     * @param $subFolder string
     * @param $fileName
     * @param $fileContent
     * @throws \ReflectionException
     */
    private function __storeFile($subFolder, $fileName, $fileContent) {
        $disk = Storage::disk('local');
        $now = Carbon::now();
        $folderName = $now->get('year') .
            str_pad($now->get('month'), 2, '0', STR_PAD_LEFT) .
            str_pad($now->get('day'), 2, '0', STR_PAD_LEFT) .
            str_pad($now->get('hour'), 2, '0', STR_PAD_LEFT) .
            str_pad($now->get('minute'), 2, '0', STR_PAD_LEFT) .
            str_pad($now->get('second'), 2, '0', STR_PAD_LEFT);
        $path = $folderName . '/';
        if (!empty($subFolder)) {
             $path .= $subFolder . '/';
        }

        $disk->put($path . ucfirst($fileName ). '.vue', $fileContent);
        return $folderName;
    }

    /**
     * @param $fieldList
     * @param $requireLang
     * @param $apiURI
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function __generateDataSection($fieldList, $apiURI, $requireLang) {
        return view('skeleton.js.data', [
            'fields' => $fieldList,
            'requireLang' => $requireLang,
            'apiURI' => $apiURI,
        ]);
    }

    /**
     * @param $fieldList
     * @param $requireLang
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function __generateMethodSection($fieldList, $requireLang) {
        return view('skeleton.js.dMethods', [
            'fields' => $fieldList,
            'requireLang' => $requireLang,
        ]);
    }

    /**
     * @param $componentName
     * @param $fieldList
     * @param $dynamicData
     * @param $dynamicMethods
     * @param $displayField
     * @param $requireLang
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function __generateFiles($componentName, $fieldList, $dynamicData, $dynamicMethods, $displayField, $requireLang) {
        return view('skeleton.template', [
            'componentName' => $componentName,
            'fields' => $fieldList,
            'dynamicData' => $dynamicData,
            'requireLang' => $requireLang,
            'dynamicMethods' => $dynamicMethods,
            'displayField' => $displayField,
        ]);
    }

    private function __dataTypeToInput($dataType) {
        switch ($dataType) {
            case 'int':
            case 'tinyint':
            case 'decimal':
                return 'number';
            case 'timestamp':
            case 'datetime':
                return 'datetime-local';
            default:
                return 'text';
        }

    }

}