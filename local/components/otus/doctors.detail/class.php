<?php

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

class OtusDoctorsDetailComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $fields = $this->arParams['DETAIL_FIELD_CODE'];
        $properties = $this->arParams['DETAIL_PROPERTY_CODE'];
        $fields = array_filter($fields);

        $properties = array_filter($properties);

        $params['select'] = self::prepareSelectParams($fields, $properties);
        $params['filter'] = ['ID' => $this->arParams['DOCTOR_ID']];

        if ($this->startResultCache()) {
            $this->SetResultCacheKeys([]);

            $names = self::getPropertyNames($properties, $fields);

            $item = self::getCompany($fields, $properties, $params);

            if (empty($item)) {
                ShowError(Loc::getMessage('OTUS_SAMPLE_GRID_NOT_FOUND'));
                $this->abortResultCache();
            }

            $this->arResult = $item;
            $this->arResult['NAMES'] = $names;


            $this->includeComponentTemplate();
        }
        global $APPLICATION;
        $APPLICATION->SetTitle(
            Loc::getMessage(
                'OTUS_SAMPLE_GRID_SHOW_TITLE',
                [
                    '#NAME#' => $item['FIELDS']['NAME'],
                ]
            )
        );
    }

    private static function prepareSelectParams($fields, $properties): array
    {
        $result = [];

        foreach ($properties as $property) {
            $result[$property . '_VALUE'] = $property . '.VALUE';
        }

        return array_merge($result, $fields);
    }

    private function getCompany(array $fields, array $properties, array $params): array
    {
        $iblock = Iblock::wakeUp($this->arParams['IBLOCK_ID'])->getEntityDataClass();

        $result = $iblock::query()
            ->setSelect($params['select'])
            ->setFilter($params['filter'])
            ->exec();

        $item = [];

        foreach ($result as $item) {
            foreach ($fields as $field) {
                $item['FIELDS'][$field] = $item[$field];
            }
            foreach ($properties as $property) {
                $item['PROPERTIES'][$property] = $item[$property . '_VALUE'];
            }
        }
        return $item;
    }

    private static function getPropertyNames(array $properties, array $fields): array
    {
        $names = [];
        $result = PropertyTable::query()
            ->setSelect(['NAME', 'CODE'])
            ->setFilter(['CODE' => $properties])
            ->exec();

        foreach ($result as $item) {
            $names[$item['CODE']] = $item['NAME'];
        }

        foreach ($fields as $field) {
            $names[$field] = Loc::getMessage('IBLOCK_FIELD_' . $field);
        }
        return $names;
    }
}