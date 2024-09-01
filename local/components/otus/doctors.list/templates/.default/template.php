<?php

/**
 * @var $this CBitrixComponentTemplate
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

$APPLICATION->SetTitle(Loc::getMessage('OTUS_SAMPLE_GRID_TITLE'));
?>

<?
$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.grid',
    'titleflex',
    [
        'GRID_ID' => $arResult['GRID_ID'],
        'HEADERS' => $arResult['HEADERS'],
        'ROWS' => $arResult['ROWS'],
        'SORT' => $arResult['SORT'],
        'FILTER' => $arResult['FILTER'],
        'IS_EXTERNAL_FILTER' => false,
        'ENABLE_LIVE_SEARCH' => $arResult['ENABLE_LIVE_SEARCH'],
        'AJAX_ID' => '',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_LOADER' => null,
        'SHOW_ROW_CHECKBOXES' => false,
        'SHOW_NAVIGATION_PANEL' => false,
    ],
    $this->getComponent(),
    ['HIDE_ICONS' => 'Y',]
); ?>