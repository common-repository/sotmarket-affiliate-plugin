<?php

/**
 * Класс виджета отображения сопутствующих товаров
 */
class Sotmarket_Related_Widget extends Sotmarket_Widget {

    //id товара к которому идет привязка
    protected $iProductId = 0;

    protected $sType = 'related';

    function Sotmarket_Related_Widget() {
        parent::WP_Widget(
            false,
            $name = 'Sotmarket Сопутствующие товары',
            array(
                'description' => 'Виджет отображения сопутствующих товаров Sotmarket'
            ),
            array(
                'width' => 400,
            )
        );
    }

    public function setCategories($aCategories = array()){
        $this->aCategories = $aCategories;
    }

    /**
     * устанавливает переменные класса значениями из базы
     * @param $instance
     */
    protected function setVariables( $instance ){

        parent::setVariables( $instance );

        if ( isset( $instance['product_cnt'] ) ){
            $this->iProductsCnt = $instance['product_cnt'];
        }
        if ( isset( $instance['product_id'] ) ){
            $this->iProductId = $instance['product_id'];
        }
        if ( isset( $instance['category_ids'] ) ){
            $this->aCategories = explode( ',', $instance['category_ids'] );
        }

    }

    /**
     * Фронтенд виджета
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        parent::widget( $args, $instance );
    }

    /**
     * Метод подбирающий аксесуары товары и отдающий их отображение
     * @return string
     */
    protected function getProducts(){

        global $post;

        $aProduct = $this->getProductIdName( $post );
        if (!$aProduct){
            return;
        }
        $iProductId = $aProduct['iProductId'];
        $sProductName = $aProduct['sProductName'];

        return $this->getProductInfo($iProductId, $sProductName, 'sotmarket_related_base');

    }

    function getProductInfo( $iProductId, $sProductName, $sTemplateName = 'sotmarket_related_base') {

        //пытаемся получить id товаров которые выводятся на странице
        try {
            $aProductIds = $this->getProductIdsByIdAndName( $iProductId, $sProductName );
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case ERROR_STATUS_INFO:
                    return '';
                default:
                    echo $e->getMessage();
            }
        }

        //если на странице нет товара - не выводим похожие
        if (!$aProductIds){
            return '';
        }

        $aRelatedProductIds = array();
        $aStatuses = $this->bShowOnlyEnabled ? array('sklad') : array();
        //товары найдены ищем похожие
        foreach( $aProductIds as $iProductId ){
            $aFindedRelatedProductIds = $this->oController->product_accessories_cached( $iProductId, $this->aCategories,$aStatuses );
            $aRelatedProductIds = array_merge( $aRelatedProductIds, $aFindedRelatedProductIds );
            //если уже получили больше товаров чем в пределе
            if ( count($aRelatedProductIds ) > $this->iProductsCnt ){

                array_splice($aRelatedProductIds, $this->iProductsCnt );
                break;
            }

        }

        if ( !$aRelatedProductIds){
            return '';
        }

        //получаем информацию о похожих товарах
        $aProductsInfo = $this->oController->product_info_array_cached($aRelatedProductIds, array('image_size' => $this->sImageSize), true);
        return $this->renderProductInfo( $aProductsInfo, $sTemplateName, $this->sImageSize );

    }

    /**
     * Опции виджета
     * @param array $instance
     */
    public function form( $instance ) {

        $this->setVariables( $instance );
        ?>
    <p>
        <label for="product_cnt">Сколько выводить:</label><br/>
        <input type="text" id="product_cnt"
               name="<?= $this->get_field_name('product_cnt') ?>" value="<?= $this->iProductsCnt ?>"/>
    </p>
    <p>
        <label for="product_id">id товара к которому идет привязка:</label><br/>
        <input type="text" id="product_id"
               name="<?= $this->get_field_name('product_id') ?>" value="<?= $this->iProductId ?>"/>
    </p>
    <p>
        <label for="category_ids">id категорий через запятую:</label><br/>
        <input type="text" id="category_ids"
               name="<?= $this->get_field_name('category_ids') ?>" value="<?= implode(',',$this->aCategories ) ?>"/>
    </p>

    <?
        $this->commonForm( );
        ?>
    <p>
        Шаблон вывода Вы можете поменять в настройках плагина. Шаблон sotmarket_related_base.
    </p>
    <?
    }

    /**
     * Сохранение параметров виджета
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['product_cnt'] = (int)$new_instance['product_cnt'] ;
        $instance['columns_cnt'] = (int)$new_instance['columns_cnt'];
        $instance['title'] = $new_instance['title'];
        $instance['product_id'] = (int)$new_instance['product_id'];
        if ($new_instance['category_ids']){
            $aNewCategories = explode(',',$new_instance['category_ids']);
            $this->aCategories= array();
            foreach($aNewCategories as $iCategory){
                $iCategory = (int)trim($iCategory);
                if ( $iCategory ){
                    $this->aCategories[] = $iCategory;
                }

            }
            if ( !$this->aCategories ){
                $this->aCategories = array(11,25);
            }
            $instance['category_ids'] = implode(',', $this->aCategories );
        }
        if ( $new_instance['show_title'] == 'on' ){
            $instance['show_title'] = 1;
        } else {
            $instance['show_title'] = 0;
        }
        $instance['show_on_front'] = 0;
        if ( $new_instance['show_on_front'] == 'on' ){
            //проверяем указан ли id или имя
            if ( $new_instance['front_product_id'] || $new_instance['front_product_name'] ){
                $instance['show_on_front'] = 1;
                $instance['front_product_id'] = $new_instance['front_product_id'];
                $instance['front_product_name'] = $new_instance['front_product_name'];
            }
        }
        $instance['show_on_categories'] = 0;
        if ( $new_instance['show_on_categories'] == 'on' ){
            //проверяем указан ли id или имя
            if ( $new_instance['categories_product_id'] || $new_instance['categories_product_name'] ){
                $instance['show_on_categories'] = 1;
                $instance['categories_product_id'] = $new_instance['categories_product_id'];
                $instance['categories_product_name'] = $new_instance['categories_product_name'];
            }
        }
        $instance['show_on_archive'] = 0;
        if ( $new_instance['show_on_archive'] == 'on' ){
            //проверяем указан ли id или имя
            if ( $new_instance['archive_product_id'] || $new_instance['archive_product_name'] ){
                $instance['show_on_archive'] = 1;
                $instance['archive_product_id'] = $new_instance['archive_product_id'];
                $instance['archive_product_name'] = $new_instance['archive_product_name'];
            }
        }
        $instance['show_on_tags'] = 0;
        if ( $new_instance['show_on_tags'] == 'on' ){
            //проверяем указан ли id или имя
            if ( $new_instance['tags_product_id'] || $new_instance['tags_product_name'] ){
                $instance['show_on_tags'] = 1;
                $instance['tags_product_id'] = (int)$new_instance['tags_product_id'];
                $instance['tags_product_name'] = $new_instance['tags_product_name'];
            }
        }

        $instance['show_on_posts'] = 0;
        if ( $new_instance['show_on_posts'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_posts'] = 1;
            $instance['posts_product_id'] = $new_instance['posts_product_id'];
            $instance['posts_product_name'] = $new_instance['posts_product_name'];

        }
        $instance['show_only_enabled'] = 0;
        if ( $new_instance['show_only_enabled'] == 'on' ){
            $instance['show_only_enabled'] = 1;
        }

        $instance['image_size'] = $new_instance['image_size'];
        $instance['subref'] = $new_instance['subref'];

        return $instance;
    }

    public function sotmarket_checkout($content) {

        $content = preg_replace_callback('!\[sotmarket_related_(.*?)\]!', array($this, 'sotmarket_widget_info'), $content);
        return $content;
    }

    public function sotmarket_widget_info($aMatches) {

        global $post;

        if ( !$aMatches[1] ){
            return '';
        }

        $iSotmarketProductID = get_post_meta($post->ID, 'sotm_product_id', true);
        $sProductName        = get_post_meta($post->ID, 'sotm_product_name', true);

        $aTemplate = explode('_',$aMatches[1]);
        $sTemplateName = 'sotmarket_related_'.$aTemplate[0];
        $this->iProductsCnt = 1;
        $sShowStyle = 'both';

        //если указано сколько выводить
        if ( isset( $aTemplate[1] ) ){
            $this->iProductsCnt = (int)$aTemplate[1];
            if (!$this->iProductsCnt){
                $this->iProductsCnt = 1;
            }
        }

        //если указано в каком виде поста выводить (анонс, полная, все)
        $aAvailableStyles = array('both','full','announce');
        if ( isset( $aTemplate[2]) && in_array( $aTemplate[2], $aAvailableStyles ) ){
            $sShowStyle = $aTemplate[2];
        }

        $bFullPost = isset($post->ancestors);

        if (
            ($bFullPost && $sShowStyle == 'announce' ) ||
            (!$bFullPost && $sShowStyle == 'full' )
        ) {
            return '';
        }
        if ( isset( $aTemplate[3]) ){
            $this->sImageSize = $aTemplate[3];
        }

        //если указаны категории
        if ( isset( $aTemplate[4] ) ){
            $sCategories = $aTemplate[4];
            $aCategories = explode( ',' ,$sCategories );
            $aSearchedCategories = array();
            foreach($aCategories as $iCategory ){
                $iCategory = (int)trim($iCategory);
                if ($iCategory){
                    $aSearchedCategories[] = $iCategory;
                }

            }
            if ( $aSearchedCategories ){
                $this->aCategories = $aSearchedCategories;
            }

        }

        if ( isset( $aTemplate[5]) ){
            $aTmpIds = explode(',',$aTemplate[5]);
            $aProductIds = array();
            foreach ($aTmpIds as $iTmpId){
                $iTmpId = (int)$iTmpId;
                if ($iTmpId){
                    $aProductIds[] = $iTmpId;
                }
            }
            if ($aProductIds){
                $iSotmarketProductID = implode(',',$aProductIds);
            }
        }
        $this->sSubref = '';
        if ( isset( $aTemplate[6]) ){
            $this->sSubref = $aTemplate[6];
        }

        $this->bShowOnlyEnabled = true;
        if (get_option('sotmarket_viewavailable_radio') == 'all'){
            $this->bShowOnlyEnabled = false;
        }

        try {
            return $this->getProductInfo( $iSotmarketProductID, $sProductName, $sTemplateName );
        } catch (Exception $e) {
            switch ($e->getCode()) {
                // отлавливаем ошибки которые не критичные
                case ERROR_STATUS_INFO:
                    return '';
                default:
                    return $e->getMessage();
            }
        }
    }
}