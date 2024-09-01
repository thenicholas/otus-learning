<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


class SampleGridComponent extends CBitrixComponent
{
    const SEF_DEFAULT_TEMPLATES = ['detail' => '#DOCTOR_ID#/'];

    public function executeComponent()
    {
        if ($this->arParams['SEF_MODE'] === 'Y') {
            if (!is_array($this->arParams['SEF_URL_TEMPLATES'])) {
                $this->arParams['SEF_URL_TEMPLATES'] = [];
            }

            $sefTemplates = array_merge(self::SEF_DEFAULT_TEMPLATES, $this->arParams['SEF_URL_TEMPLATES']);
            $page = CComponentEngine::parseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $sefTemplates,
                $arVariables,
            );

            $this->arResult = [
                'SEF_FOLDER' => $this->arParams['SEF_FOLDER'],
                'SEF_URL_TEMPLATES' => $sefTemplates,
                'VARIABLE_ALIASES' => $arVariables,
            ];
        } else {
            $arDefaultVariableAliases = [];
            $arComponentVariables = ['DOCTOR_ID'];
            $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
                $arDefaultVariableAliases,
                $this->arParams['VARIABLE_ALIASES']
            );

            $arVariables = [];

            CComponentEngine::initComponentVariables(
                false,
                $arComponentVariables,
                $arVariableAliases,
                $arVariables
            );

            if ((isset($arVariables['DOCTOR_ID']) && intval(
                        $arVariables['DOCTOR_ID']
                    ) > 0) || (isset($arVariables['DOCTOR_CODE']) && $arVariables['DOCTOR_CODE'] != '')) {
                $page = 'detail';
            }

            $this->arResult = [
                'VARIABLE_ALIASES' => $arVariables,
            ];
        }

        $this->arResult['SEF_MODE'] = $this->arParams['SEF_MODE'];

        if (empty($page)) {
            $page = 'list';
        }

        $this->IncludeComponentTemplate($page);
    }

}
