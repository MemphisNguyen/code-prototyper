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

    public function handleSubmit(Request $request) {
        $iData = $request->validate([
            'name' => 'required|string',
            'sub_folder' => 'nullable|string',
            'table' => 'required|string',
            'mul_lang' => 'nullable|boolean',
            'sub_table' => 'required_with:mul_lang',
            'need_parent_id' => 'nullable|boolean',
            'api_uri' => 'required|string',
            'display_field' => 'required|string',
            'sub_field' => 'nullable|string',
        ]);
        $iData['name'] = preg_replace('/[^[:alnum:]]+/', ' ', $iData['name']);
        $iData['sub_folder'] = trim(ucfirst(preg_replace('/[^[:alnum:]]+/', '', $iData['sub_folder'])));
        $columns = Table::getColumns($iData['table'])->toArray();
        if (isset($iData['mul_lang'])) {
            $columns = array_merge($columns, Table::getColumns($iData['sub_table'])->toArray());
        }
        if (empty($columns)) {
            return redirect('/')->withErrors('Table not found or without any column');
        }
        $fields = array();

        foreach ($columns as $col) {
            $fields[$col->COLUMN_NAME] = $col->DATA_TYPE;
        }
        $folderName = $this->__prototyping($iData['name'], $iData['sub_folder'], $fields, $iData['api_uri'],
            $iData['display_field'], isset($iData['mul_lang']),
            isset($iData['sub_field']) ? $iData['sub_field'] : '',
            isset($iData['need_parent_id']) ? strtolower($iData['sub_folder']) . '_id' : false);

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
     * @param bool|string $parentIdField
     * @return string
     */
    private function __prototyping($componentName, $subFolder, $fieldList, $apiURI, $displayField, $requireLang,
                                   $displaySubField = '', $parentIdField = false) {
        foreach ($fieldList as $field => $type) {
            $fieldList[$field] = $this->__dataTypeToInput($type);
        }
        $formattedCompName = str_replace(' ', '', $componentName);
        $fileName = str_replace(' ', '', $componentName);
        $listFile = $this->__generateListFile($componentName, $formattedCompName, $subFolder, $fieldList, $displayField,
            $requireLang, $displaySubField, $parentIdField);
        $formFile = $this->__generateFormFile($subFolder, $formattedCompName, $fieldList, $requireLang, $parentIdField);
        $routesFile = $this->__generateRoutesFile($componentName, $formattedCompName, $subFolder, $parentIdField);
        return $this->__storeFile($subFolder, $fileName, $listFile, $formFile, $routesFile);
}

    /**
     * @param $subFolder string
     * @param $fileName
     * @param $listFile
     * @param $fileForm
     * @param $fileRoutes
     * @return string
     */
    private function __storeFile($subFolder, $fileName, $listFile, $fileForm, $fileRoutes) {
        try {
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
            $path = $path . '/' . $fileName . '/';

            $disk->put($path . $fileName . 'List.vue', $listFile);
            $disk->put($path . $fileName . 'Form.vue', $fileForm);
            $disk->put($path . 'routes.js', $fileRoutes);
            return $folderName;
        } catch (\ReflectionException $e) {
            dd($e);
        }
    }

    /**
     * @param $componentName
     * @param $formattedCompName
     * @param $subFolder
     * @param $fieldList
     * @param $displayField
     * @param $requireLang
     * @param $displaySubField
     * @param $parentIdField
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function __generateListFile($componentName, $formattedCompName, $subFolder, $fieldList, $displayField,
                                     $requireLang, $displaySubField, $parentIdField) {
        return view('skeleton.template-list', [
            'componentName' => $componentName,
            'formattedCompName' => $formattedCompName,
            'containFolder' => $subFolder,
            'fieldList' => $fieldList,
            'displayField' => $displayField,
            'requireLang' => $requireLang,
            'displaySubField' => $displaySubField,
            'parentIdField' => $parentIdField
        ]);
    }

    /**
     * @param $containFolder
     * @param $formattedCompName
     * @param $fieldList
     * @param $requireLang
     * @param $parentIdField
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function __generateFormFile($containFolder, $formattedCompName, $fieldList, $requireLang, $parentIdField)
    {
        return view('skeleton.template-form', [
            'containFolder' => $containFolder,
            'formattedCompName' => $formattedCompName,
            'fieldList' => $fieldList,
            'requireLang' => $requireLang,
            'parentIdField' => $parentIdField
        ]);
    }

    /**
     * @param $componentName
     * @param $formattedCompName
     * @param $subFolder
     * @param $parentIdField
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function __generateRoutesFile($componentName, $formattedCompName, $subFolder, $parentIdField)
    {
        return view('skeleton.template-routes', [
            'componentName' => $componentName,
            'formattedCompName' => $formattedCompName,
            'containFolder' => $subFolder,
            'parentIdField' => $parentIdField
        ]);
    }

    /**
     * Convert column type to input type
     *
     * @param $dataType
     * @return string
     */
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
