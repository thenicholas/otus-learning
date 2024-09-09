<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

class OtusDoctorsListComponent extends CBitrixComponent
{

    const GRID_ID = 'TEST_LIST';

    public static array $fields = [];

    public static array $properties = [];

    public function executeComponent(): void
    {
        $fields = $this->arParams['LIST_FIELD_CODE'];
        $properties = $this->arParams['LIST_PROPERTY_CODE'];

        self::$fields = array_filter($fields, fn($value) => $value !== '');
        if (!array_key_exists('ID', self::$fields)) {
            self::$fields[] = 'ID';
        }
        $properties = array_filter($properties, fn($value) => $value !== '');

        if ($properties) {
            self::$properties = self::getPropertiesFromParams($properties);
        }

        $fieldsAndProperties = array_merge(self::$fields, array_keys(self::$properties));

        $names = self::getNames();

        $gridHeaders = self::prepareHeaders($names);

        $gridFilterFields = self::prepareFilterFields($fieldsAndProperties, $names);

        $gridSortValues = self::prepareSortParams($fieldsAndProperties);

        $gridFilterValues = self::prepareFilterParams($gridFilterFields, $fieldsAndProperties);

        $params = self::prepareQueryParams($gridFilterValues, $gridSortValues);

        $items = self::getDoctors($params);

        $rows = self::getRows($items, $this->arParams, $fieldsAndProperties);

        $this->arResult = [
            'ITEMS' => $items,
            'GRID_ID' => self::GRID_ID,
            'HEADERS' => $gridHeaders,
            'ROWS' => $rows,
            'SORT' => $gridSortValues,
            'FILTER' => $gridFilterFields,
            'ENABLE_LIVE_SEARCH' => false,
            'DISABLE_SEARCH' => true,
        ];

        $this->IncludeComponentTemplate();
    }

    private function getDoctors(array $params): array
    {
        $iblock = \Bitrix\Iblock\Iblock::wakeUp($this->arParams['IBLOCK_ID'])->getEntityDataClass();

        $result = $iblock::query()
            ->setSelect($params['select'])
            ->addSelect('PROCEDURES.ELEMENT.NAME')
            ->addSelect('PROCEDURES.ELEMENT.COLOR.VALUE')
            ->setOrder($params['sort'])
            ->setFilter($params['filter'])
            ->fetchCollection();

        $arItems = [];

        foreach ($result as $key => $doctor) {
            foreach (self::$fields as $field) {
                $arItems[$key][$field] = $doctor->get($field);
            }

            foreach (self::$properties as $property) {
                switch ($property['TYPE']) {
                    case 'E':
                        break;
                    default:
                        $arItems[$key][$property['CODE']] = $doctor->get($property['CODE'])->getValue();
                }
            }

            $procedures = $doctor->getProcedures();
            $procedureNames = [];

            foreach ($procedures as $procedure) {
                $procedureName = $procedure->getElement()->getName();
                $colors = $procedure->getElement()->getColor();

                $colorValues = [];

                foreach ($colors as $color) {
                    $colorValues[] = $color->getValue();
                }

                $colorsHtml = array_reduce($colorValues, function ($string, $color) {
                    $string .= "<span style='background-color: {$color}; width: 10px; height: 10px; margin-right: 4px; position: relative; top: 0px; display: inline-block;'></span>";
                    return $string;
                }, '');

                $procedureNames[] = $colorsHtml . $procedureName;
            }

            $arItems[$key]['PROCEDURES'] = implode(', ', $procedureNames);
        }

        return $arItems;
    }

    private static function getRows(array $items, array $arParams, array $fieldsAndProperties): array
    {
        $rows = [];

        foreach ($items as $key => $item) {
            if ($arParams['SEF_MODE'] === 'Y') {
                $viewUrl = CComponentEngine::makePathFromTemplate(
                    $arParams['URL_TEMPLATES']['DETAIL'],
                    ['DOCTOR_ID' => $item['ID']]
                );
            } else {
                $viewUrl = $arParams['URL_TEMPLATES']['DETAIL'] . '?DOCTOR_ID=' . $item['ID'];
            }

            $rows[$key] = [
                'id' => $item['ID'],
                'data' => $item,
            ];

            foreach ($fieldsAndProperties as $column) {
                if ($column === 'NAME') {
                    $value = '<a href="' . htmlspecialcharsEx(
                            $viewUrl
                        ) . '" target="_self">' . $item['NAME'] . '</a>';
                } else {
                    $value = $item[$column];
                }
                $rows[$key]['columns'][$column] = $value;
            }
        }

        return $rows;
    }

    private static function prepareProperties(array $props): array
    {
        if (empty($props)) {
            return $props;
        }

        $result = [];

        foreach ($props as $key => $value) {
            if (in_array($key, array_keys(self::$properties))) {
                $result[$key . '.VALUE'] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private
    static function prepareSelectParams(): array
    {
        $result = [];

        foreach (self::$properties as $property) {
            switch ($property['TYPE']) {
                case 'E':
                    //$result[] = $property['CODE'] . '.ELEMENT';
                    break;
                default:
                    $result [] = $property['CODE'];
            }
        }

        return array_merge($result, self::$fields);
    }

    private static function prepareQueryParams(array $gridFilterValues, array $gridSortValues): array
    {
        return [
            'select' => self::prepareSelectParams(),
            'filter' => self::prepareProperties($gridFilterValues),
            'sort' => self::prepareProperties($gridSortValues),
        ];
    }

    private static function prepareSortParams(array $fieldsAndProperties): array
    {
        $grid = new Bitrix\Main\Grid\Options(self::GRID_ID);

        $gridSortValues = $grid->getSorting();

        $gridSortValues = array_filter(
            $gridSortValues['sort'],
            function ($field) use ($fieldsAndProperties) {
                return in_array($field, $fieldsAndProperties);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (empty($gridSortValues)) {
            $gridSortValues = ['ID' => 'asc'];
        }

        return $gridSortValues;
    }

    private static function prepareFilterParams($filterFields, $fieldsAndProperties): array
    {
        $gridFilter = new Bitrix\Main\UI\Filter\Options(self::GRID_ID);
        $gridFilterValues = $gridFilter->getFilter($filterFields);

        return array_filter(
            $gridFilterValues,
            function ($fieldName) use ($fieldsAndProperties) {
                return in_array($fieldName, $fieldsAndProperties);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private static function prepareHeaders($names): array
    {
        $headers = [];


        foreach ($names as $field => $name) {
            $headers[] = [
                'id' => $field,
                'name' => $name,
                'sort' => $field,
                'first_order' => 'desc',
                'default' => true,
            ];
        }

        return $headers;
    }

    private static function prepareFilterFields(array $fieldsAndProperties, array $names): array
    {
        $filterFields = [];

        foreach ($fieldsAndProperties as $field) {
            if (!empty($field)) {
                $filterFields[] = [
                    'id' => $field,
                    'name' => $names[$field],
                    'sort' => $field,
                    'first_order' => 'desc',
                    'default' => true,
                ];
            }
        }

        return $filterFields;
    }

    private static function getFieldNames(): array
    {
        $names = [];
        foreach (self::$fields as $field) {
            $names[$field] = Loc::getMessage('IBLOCK_FIELD_' . $field);
        }
        return $names;
    }

    private static function getPropertiesNames(): array
    {
        $names = [];
        $result = PropertyTable::query()
            ->setSelect(['NAME', 'CODE'])
            ->setFilter(['CODE' => array_keys(self::$properties)])
            ->exec();

        foreach ($result as $item) {
            $names[$item['CODE']] = $item['NAME'];
        }
        return $names;
    }

    private static function getNames(): array
    {
        $fieldNames = self::getFieldNames();
        $propertiesNames = self::getPropertiesNames();
        return array_merge($fieldNames, $propertiesNames);
    }

    private function getPropertiesFromParams(
        mixed $properties
    ): array {
        $properties = PropertyTable::getList([
            'select' => ['ID', 'CODE', 'PROPERTY_TYPE', 'USER_TYPE'],  // Указываем поля, которые нужно выбрать
            'filter' => [
                '=IBLOCK_ID' => $this->arParams['IBLOCK_ID'],  // ID инфоблока
                '=CODE' => $properties,  // Символьный код свойства
            ],
        ])->fetchAll();

        $arProperties = [];

        foreach ($properties as $property) {
            $arProperties[$property['CODE']] = [
                'CODE' => $property['CODE'],
                'TYPE' => $property['PROPERTY_TYPE'],
            ];
        }

        return $arProperties;
    }
}
