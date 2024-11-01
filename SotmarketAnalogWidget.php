<?php

/**
 * Виджет отображения похожих товаров
 */
class Sotmarket_Analog_Widget extends Sotmarket_Widget {

    //количество выводимых товаров
    protected $sType = 'analog';


    function Sotmarket_Analog_Widget() {
        parent::WP_Widget(
            false,
            $name = 'Sotmarket Похожие товары',
            array(
                'description' => 'Виджет отображения похожих товаров Sotmarket'
            ),
            array(
                'width' => 400,
            )
        );
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
     * Метод подбирающий похожие товары и отдающий их отображение
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


        return $this->getProductInfo($iProductId, $sProductName, 'sotmarket_analog_base');

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
        $instance['product_template'] = $new_instance['product_template'];
        $instance['columns_cnt'] = (int)$new_instance['columns_cnt'];
        $instance['title'] = $new_instance['title'];
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
                $instance['tags_product_id'] = $new_instance['tags_product_id'];
                $instance['tags_product_name'] = $new_instance['tags_product_name'];
            }
        }
        $instance['show_on_posts'] = 0;
        if ( $new_instance['show_on_posts'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_posts'] = 1;
            $instance['posts_product_id'] = (int)$new_instance['posts_product_id'];
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
    <?
        $this->commonForm( );
        ?>
    <p>
        Шаблон вывода Вы можете поменять в настройках плагина. Шаблон sotmarket_analog_base.
    </p>
    <?
    }


    public function sotmarket_checkout($content) {
        global $post;
        $sPluginType     = stripslashes(get_option('sotmarket_ordertype_radio'));
        $iCheckoutPageId = get_option('sotmarket_checkout_page_id');
        if ($iCheckoutPageId == $post->ID && $sPluginType == 'cart') {
            return '';
        }
        $content = preg_replace_callback('!\[sotmarket_analog_(.*?)\]!', array($this, 'sotmarket_widget_info'), $content);
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
        $sTemplateName = 'sotmarket_analog_'.$aTemplate[0];
        $iCnt = 1;
        $sShowStyle = 'both';

        //если указано сколько выводить
        if ( isset( $aTemplate[1] ) ){
            $iCnt = (int)$aTemplate[1];
            if (!$iCnt){
                $iCnt = 1;
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

        $this->sImageSize = 'default';
        if ( isset( $aTemplate[3]) ){
            $this->sImageSize = $aTemplate[3];
        }

        if ( isset( $aTemplate[4]) ){
            $aTmpIds = explode(',',$aTemplate[4]);
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
        if ( isset( $aTemplate[5]) ){
            $this->sSubref = $aTemplate[5];
        }

        $this->bShowOnlyEnabled = true;
        if (get_option('sotmarket_viewavailable_radio') == 'all'){
            $this->bShowOnlyEnabled = false;
        }



        try {
            return $this->getProductInfo( $iSotmarketProductID, $sProductName, $sTemplateName, $iCnt );
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

    function getProductInfo( $iProductId, $sProductName, $sTemplateName = 'sotmarket_analog_base') {

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

        $aStatuses = $this->bShowOnlyEnabled ? array('sklad') : array();
        $aAnalogProductIds = array();
        //товары найдены ищем похожие
        foreach( $aProductIds as $iProductId ){
            $aFindedAnalogProductIds = $this->oController->products_analog_cached( $iProductId, array(11,25),$aStatuses);
            $aAnalogProductIds = array_merge( $aAnalogProductIds, $aFindedAnalogProductIds );
            //если уже получили больше товаров чем в пределе
            if ( count($aAnalogProductIds ) > $this->iProductsCnt ){

                array_splice($aAnalogProductIds, $this->iProductsCnt );
                break;
            }

        }

        if ( !$aAnalogProductIds){
            return '';
        }

        //получаем информацию о похожих товарах
        $aProductsInfo = $this->oController->product_info_array_cached($aAnalogProductIds, array('image_size' => $this->sImageSize), true);
        return $this->renderProductInfo( $aProductsInfo, $sTemplateName, $this->sImageSize );

    }

}


