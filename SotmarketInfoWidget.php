<?php

class Sotmarket_Info_Widget extends Sotmarket_Widget {

    //количество выводимых товаров
    protected $sType = 'products';

    function Sotmarket_Info_Widget() {
        parent::WP_Widget(
            false,
            $name = 'Sotmarket вывод товаров',
            array(
                'description' => 'Виджет отображения товаров Sotmarket'
            ),
            array(
                'width' => 400,
            )
        );
    }

    /**
     * This method creates widget frontend.
     */
    function widget($args, $instance) {
        parent::widget( $args, $instance );
    }

    function getProducts() {
        global $post;

        $aProduct = $this->getProductIdName( $post );
        if (!$aProduct){
            return;
        }
        $iProductId = $aProduct['iProductId'];
        $sProductName = $aProduct['sProductName'];


        try {
            return $this->getProductInfo( $iProductId, $sProductName );
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case ERROR_STATUS_INFO:
                    return;
                default:
                    echo $e->getMessage();
            }
        }
    }

    /**
     * устанавливает переменные класса значениями из базы
     * @param $instance
     */
    protected function setVariables( $instance ){

        parent::setVariables( $instance );
        if ( isset( $instance['category_ids'] ) ){
            $this->aCategories = explode( ',', $instance['category_ids'] );
        }
    }

    function getProductInfo($iProductId, $sProductName, $sTemplateName = 'sotmarket_info_base') {

        $aProductsIds = array();
        //в id могут быть товары через запятую
        if ( strpos( $iProductId, ',') ){
            $aSearchingProducts = explode(',',$iProductId);
            foreach( $aSearchingProducts as $iProductId){
                $iProductId = (int)$iProductId;
                if ( $iProductId ){
                    $aProductsIds[] = $iProductId;
                }
            }
        } else {
            $iSotmarketProductID = (int)$iProductId;
            //получаем id товаров по имени или названию
            $aProductsIds = $this->getProductIdsByIdAndName( $iSotmarketProductID, $sProductName );

        }

        if (count($aProductsIds) == 0) {
            throw new Exception('По поисковому запросу :' . $sProductName . ' ничего не найдено', ERROR_STATUS_INFO);
        }
        try {
            $aParams = array('image_size' => $this->sImageSize );

            $aProductsInfo = $this->oController->product_info_array_cached($aProductsIds, $aParams , true);
            if (count($aProductsInfo) == 0) {
                throw new Exception('По id :' . implode(',', $aProductsIds) . ' ничего не найдено', ERROR_STATUS_INFO);
            }

            return $this->renderProductInfo( $aProductsInfo, $sTemplateName, $this->sImageSize );
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), ERROR_STATUS_RPC);
        }
    }

    public function sotmarket_checkout($content) {

        $content = preg_replace_callback('!\[sotmarket_info_(.*?)\]!', array($this, 'sotmarket_widget_info'), $content);
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
        $sTemplateName = 'sotmarket_info_'.$aTemplate[0];
        $sShowStyle = 'both';

        //если указано в каком виде поста выводить (анонс, полная, все)
        $aAvailableStyles = array('both','full','announce');
        if ( isset( $aTemplate[1]) && in_array( $aTemplate[1], $aAvailableStyles ) ){
            $sShowStyle = $aTemplate[1];
        }

        $bFullPost = isset($post->ancestors);
        if (
            ($bFullPost && $sShowStyle == 'announce' ) ||
            (!$bFullPost && $sShowStyle == 'full' )
        ) {
            return '';
        }

        $this->sImageSize = 'default';
        if ( isset( $aTemplate[2]) ){
            $this->sImageSize = $aTemplate[2];
        }

        if ( isset( $aTemplate[3]) ){
            $aTmpIds = explode(',',$aTemplate[3]);
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

        $this->aCategories = array();
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
                $iSotmarketProductID = 0;
                $sProductName = '';
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

    /**
     * Сохранение параметров виджета
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['product_template'] = $new_instance['product_template'];
        $instance['columns_cnt'] = (int)$new_instance['columns_cnt'];
        $instance['title'] = $new_instance['title'];
        if ( $new_instance['show_title'] == 'on' ){
            $instance['show_title'] = 1;
        } else {
            $instance['show_title'] = 0;
        }
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
        $instance['show_on_front'] = 0;
        if ( $new_instance['show_on_front'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_front'] = 1;
            $instance['front_product_id'] = $new_instance['front_product_id'];
            $instance['front_product_name'] = $new_instance['front_product_name'];

        }
        $instance['show_on_categories'] = 0;
        if ( $new_instance['show_on_categories'] == 'on' ){
            //проверяем указан ли id или имя

           $instance['show_on_categories'] = 1;
           $instance['categories_product_id'] = $new_instance['categories_product_id'];
           $instance['categories_product_name'] = $new_instance['categories_product_name'];

        }
        $instance['show_on_archive'] = 0;
        if ( $new_instance['show_on_archive'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_archive'] = 1;
            $instance['archive_product_id'] = $new_instance['archive_product_id'];
            $instance['archive_product_name'] = $new_instance['archive_product_name'];

        }
        $instance['show_on_tags'] = 0;
        if ( $new_instance['show_on_tags'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_tags'] = 1;
            $instance['tags_product_id'] = $new_instance['tags_product_id'];
            $instance['tags_product_name'] = $new_instance['tags_product_name'];

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

    /**
     * Опции виджета
     * @param array $instance
     */
    public function form( $instance ) {

        $this->setVariables( $instance );

        ?>
    <p>
        <label for="columns_cnt">Сколько колонок:</label><br/>
        <input type="text" id="columns_cnt"
               name="<?= $this->get_field_name('columns_cnt') ?>" value="<?= $this->iColumnsCnt ?>"/>
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
        Шаблон вывода Вы можете поменять в настройках плагина. Шаблон sotmarket_info_base.
    </p>
    <?
    }
}
