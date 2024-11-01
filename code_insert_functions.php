<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cloud
 * Date: 25.01.13
 * Time: 14:10
 * Функции вставки блоков с помощью php кода
 */

/**
 * Вывод товарного блока
 * @param string $sTemplateName
 * @param int $iCnt
 * @param string $sImageSize
 * @param array $aCategories
 * @param array $aProductsId
 */
function getSotmarketInfo( $sTemplateName = 'sotmarket_info_base', $iCnt = 1, $sImageSize='default',$aCategories = array(), $aProductsId = array()){
    global $oSotmarketWP;

    _getSotmarketProduct($oSotmarketWP,$sTemplateName,$iCnt,$sImageSize,$aCategories,$aProductsId);
}

/**
 * Функция вывода аналогичных товаров из кода
 * @param string $sTemplateName
 * @param int $iCnt
 * @param string $sImageSize
 * @param array $aCategories
 * @param array $aProductsId
 */
function getSotmarketAnalog( $sTemplateName = 'sotmarket_analog_base', $iCnt = 1, $sImageSize='default',$aCategories = array(), $aProductsId = array()) {
    global $oSotmarketAnalogWidget;

    _getSotmarketProduct($oSotmarketAnalogWidget,$sTemplateName,$iCnt,$sImageSize,$aCategories,$aProductsId);
}

/**
 * Функция вывода сопутствующих товаров из кода
 * @param string $sTemplateName
 * @param int $iCnt
 * @param string $sImageSize
 * @param array $aCategories
 * @param array $aProductsId
 */
function getSotmarketRelated( $sTemplateName = 'sotmarket_analog_base', $iCnt = 1, $sImageSize='default',$aCategories = array(), $aProductsId = array()) {
    global $oSotmarketRelatedWidget;

    _getSotmarketProduct($oSotmarketRelatedWidget,$sTemplateName,$iCnt,$sImageSize,$aCategories,$aProductsId);
}

function getSotmarketPopular($sTemplateName = 'sotmarket_popular_base', $iCnt = 1, $sImageSize='default',$aCategories = array(),$iBrandId){
    global $oSotmarketPopularWidget;
    try {
        $oSotmarketPopularWidget->sImageSize = $sImageSize;
        $oSotmarketPopularWidget->aCategories = $aCategories;
        $oSotmarketPopularWidget->iProductsCnt = $iCnt;
        $oSotmarketPopularWidget->iBrandId = $iBrandId;
        echo $oSotmarketPopularWidget->getProductInfo($sTemplateName );
    } catch (Exception $e) {
        echo $e->getMessage();
    }

}

/**
 * Универсальная функция вставки блока
 * @param $oWidget
 * @param $sTemplateName
 * @param int $iCnt
 * @param string $sImageSize
 * @param array $aCategories
 * @param array $aProductsId
 *
 */
function _getSotmarketProduct($oWidget , $sTemplateName, $iCnt = 1 , $sImageSize='default',$aCategories = array(), $aProductsId = array()){
    global $post;

    if ($aProductsId){
        $iSotmarketProductID = implode(',',$aProductsId);
    } else {
        $iSotmarketProductID = get_post_meta($post->ID, 'sotm_product_id', true);
    }
    $sProductName        = get_post_meta($post->ID, 'sotm_product_name', true);
    try {
        $oWidget->sImageSize = $sImageSize;
        $oWidget->aCategories = $aCategories;
        $oWidget->iProductsCnt = $iCnt;
        echo $oWidget->getProductInfo($iSotmarketProductID, $sProductName, $sTemplateName );
    } catch (Exception $e) {
        echo $e->getMessage();
    }

}