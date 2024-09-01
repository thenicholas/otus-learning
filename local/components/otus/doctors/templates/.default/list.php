<?php

/**
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var array $arParams
 * @var array $arResult
 */

$urlTemplates = [
    'DETAIL' => $arParams['SEF_FOLDER'] . $arParams['SEF_URL_TEMPLATES']['detail'],
];

$APPLICATION->IncludeComponent(
    'otus:doctors.list',
    '',
    [
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'URL_TEMPLATES' => $urlTemplates,
        'SEF_FOLDER' => $arResult['SEF_FOLDER'],
        'SEF_MODE' => $arResult['SEF_MODE'],
        'LIST_FIELD_CODE' => $arParams['LIST_FIELD_CODE'],
        'LIST_PROPERTY_CODE' => $arParams['LIST_PROPERTY_CODE'],
    ],
    $this->getComponent(),
    [
        'HIDE_ICONS' => 'Y',
    ]
);
