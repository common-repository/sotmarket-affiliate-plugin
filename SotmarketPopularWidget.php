<?php

class Sotmarket_Popular_Widget extends Sotmarket_Widget {

    //количество выводимых товаров
    protected $sType = 'popular';
    public $iBrandId = 0;


    function Sotmarket_Popular_Widget() {
        parent::WP_Widget(
            false,
            $name = 'Sotmarket вывод популярных товаров товаров',
            array(
                'description' => 'Виджет отображения популярных товаров Sotmarket'
            ),
            array(
                'width' => 400,
            )
        );
    }
    function widget($args, $instance) {
        parent::widget( $args, $instance );
    }

    public function sotmarket_checkout($content) {

        $content = preg_replace_callback('!\[sotmarket_popular_(.*?)\]!', array($this, 'sotmarket_widget_info'), $content);
        return $content;
    }

    public function sotmarket_widget_info($aMatches) {
        global $post;
        if ( !$aMatches[1] ){
            return '';
        };
        $aTemplate = explode('_',$aMatches[1]);
        $sTemplateName = 'sotmarket_popular_'.$aTemplate[0];
        $this->iProductsCnt = 1;
        $sShowStyle = 'both';

        //если указано сколько выводить
        if ( isset( $aTemplate[1] ) ){
            $this->iProductsCnt = (int)$aTemplate[1];
            if ( !$this->iProductsCnt){
                $this->iProductsCnt= 1;
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

        $this->iBrandId = 0;
        if ( isset( $aTemplate[5]) ){
            $this->iBrandId = (int)$aTemplate[5];
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
            return $this->getProductInfo( $sTemplateName);
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
     * Опции виджета
     * @param array $instance
     */
    public function form( $instance ) {

        $this->setVariables( $instance );

        ?>
    <p>
        <label for="columns_cnt">Сколько товаров выводить:</label><br/>
        <input type="text" id="product_cnt"
               name="<?= $this->get_field_name('product_cnt') ?>" value="<?= $this->iProductsCnt ?>"/>
    </p>
    <p>
        <label for="columns_cnt">Сколько колонок:</label><br/>
        <input type="text" id="columns_cnt"
               name="<?= $this->get_field_name('columns_cnt') ?>" value="<?= $this->iColumnsCnt ?>"/>
    </p>
    <p>
        <label for="category_ids">id категорий через запятую:</label><br/>
        <input type="text" id="category_ids" name="<?= $this->get_field_name('category_ids') ?>" value="<?= implode(',',$this->aCategories ) ?>"/>
    </p>
    <p>
        <label for="category_ids">id бренда:</label><br/>
        <input type="text" id="brand_id" name="<?= $this->get_field_name('brand_id') ?>" value="<?= $this->iBrandId ? $this->iBrandId : '' ?>"/>
    </p>
    <p>
        <label for="title">Заголовок блока:</label><br/>
        <input type="text" id="title"
               name="<?= $this->get_field_name('title') ?>" value="<?= trim($this->sTitle) ?>" /><br/>
        <input type="checkbox" id="show_title"
               name="<?= $this->get_field_name('show_title') ?> " <?= $this->bShowTitle ? 'checked' : '' ?> >
        <label for="show_title">Показывать заголовок</label>
    </p>
    <p>
        <input type="checkbox" id="show_on_front"
               name="<?= $this->get_field_name('show_on_front') ?>" <?= $this->bShowOnFrontPage ? 'checked' : '' ?> >
        <label for="show_on_front"> Показывать на главной</label><br/>
    </p>
    <p>
        <input type="checkbox" id="show_on_categories"
               name="<?= $this->get_field_name('show_on_categories') ?>" <?= $this->bShowOnCategories ? 'checked' : '' ?> >
        <label for="show_on_categories"> Показывать в категориях</label><br/>
    </p>
    <p>
        <input type="checkbox" id="show_on_archive"
               name="<?= $this->get_field_name('show_on_archive') ?>" <?= $this->bShowOnArchive ? 'checked' : '' ?> >
        <label for="show_on_categories"> Показывать в архивах</label><br/>
    </p>
    <p>
        <input type="checkbox" id="show_on_tags"
               name="<?= $this->get_field_name('show_on_tags') ?>" <?= $this->bShowOnTags ? 'checked' : '' ?> >
        <label for="show_on_categories"> Показывать в тэгах</label><br/>
    </p>
    <p>
        <input type="checkbox" id="show_on_posts"
               name="<?= $this->get_field_name('show_on_posts') ?>" <?= $this->bShowInPosts ? 'checked' : '' ?> >
        <label for="show_on_posts"> Показывать в полной новости</label><br/>
    </p>
    <hr>
    <p>
        <input type="checkbox" id="show_only_enabled"
               name="<?= $this->get_field_name('show_only_enabled') ?>" <?= $this->bShowOnlyEnabled ? 'checked' : '' ?> >
        <label for="show_only_enabled"> Показывать только товары в наличии</label><br/>
    </p>
    <p>
        <label for="image_size">Размер картинок</label>
        <select id="image_size" name="<?= $this->get_field_name('image_size')?>">
            <option value="default" <?= $this->sImageSize == 'default' ? 'selected' : '' ?> >Стандартные</option>
            <option value="100x100" <?= $this->sImageSize == '100x100' ? 'selected' : '' ?> >100x100</option>
            <option value="140x200" <?= $this->sImageSize == '140x200' ? 'selected' : '' ?> >140x200</option>
            <!--<option value="300x250" <?= ''//$this->sImageSize == '300x250' ? 'selected' : '' ?> >300x250</option>-->
            <option value="1200x1200" <?= $this->sImageSize == '1200x1200' ? 'selected' : '' ?> >1200x1200</option>
            <option value="100x150" <?= $this->sImageSize == '100x150' ? 'selected' : '' ?> >100x150</option>
            <option value="50x50" <?= $this->sImageSize == '50x50' ? 'selected' : '' ?> >50x50</option>
        </select>
    </p>
    <p>
        <label for="subref">значение subref:</label> <br/>
        <input type="text" id="subref"
               name="<?= $this->get_field_name('subref') ?>" value="<?= $this->sSubref ?>" /><br/>
    </p>

    <p>
        Шаблон вывода Вы можете поменять в настройках плагина. Шаблон sotmarket_popular_base.
    </p>
    <?
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['product_template'] = $new_instance['product_template'];
        $instance['columns_cnt'] = (int)$new_instance['columns_cnt'];
        $instance['product_cnt'] = (int)$new_instance['product_cnt'] ;
        $instance['brand_id'] = (int)$new_instance['brand_id'];
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


        }
        $instance['show_on_categories'] = 0;
        if ( $new_instance['show_on_categories'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_categories'] = 1;


        }
        $instance['show_on_archive'] = 0;
        if ( $new_instance['show_on_archive'] == 'on' ){
            //проверяем указан ли id или имя

            $instance['show_on_archive'] = 1;


        }
        $instance['show_on_tags'] = 0;
        if ( $new_instance['show_on_tags'] == 'on' ){
            //проверяем указан ли id или имя
            $instance['show_on_tags'] = 1;
        }

        $instance['show_on_posts'] = 0;
        if ( $new_instance['show_on_posts'] == 'on' ){
            $instance['show_on_posts'] = 1;
        }

        $instance['show_only_enabled'] = 0;
        if ( $new_instance['show_only_enabled'] == 'on' ){
            $instance['show_only_enabled'] = 1;

        }

        $instance['image_size'] = $new_instance['image_size'];

        $instance['subref'] = $new_instance['subref'];

        return $instance;
    }

    function getProducts() {
        global $post;
        if ((is_home() || is_front_page()) && !$this->bShowOnFrontPage) {
            return;
        } elseif ( is_category() && !$this->bShowOnCategories ) {
            return;
        } elseif ( is_archive() && !$this->bShowOnArchive){
            return;
        } elseif ( is_tag() && !$this->bShowOnTags ){
            return;
        } elseif ( is_single() && !$this->bShowInPosts) {
            return;
        } else{

        }

        try {
            return $this->getProductInfo( );
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case ERROR_STATUS_INFO:
                    return;
                default:
                    echo $e->getMessage();
            }
        }
    }
    function getProductInfo( $sTemplateName = 'sotmarket_popular_base' ) {

        $aProductsIds = $this->oController->product_search_cached( '' , $this->aCategories,array(),'popularity','asc' , $this->iBrandId ? array( $this->iBrandId ): array() );
        $aProductsIds = array_slice($aProductsIds,0,$this->iProductsCnt);

        try {
            $aParams = array(
                'image_size' => $this->sImageSize,
                'order_by' => 'popularity',
                'sort_by' => 'asc',
            );

            $aProductsInfo = $this->oController->product_info_array_cached($aProductsIds, $aParams , true);
            if (count($aProductsInfo) == 0) {
                throw new Exception('По id :' . implode(',', $aProductsIds) . ' ничего не найдено', ERROR_STATUS_INFO);
            }

            return $this->renderProductInfo( $aProductsInfo, $sTemplateName, $this->sImageSize );
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), ERROR_STATUS_RPC);
        }
    }

    /**
     * устанавливает переменные класса значениями из базы
     * @param $instance
     */
    protected function setVariables( $instance ){

        parent::setVariables( $instance );

        if (isset($instance['show_on_posts'])){
            $this->bShowInPosts = $instance['show_on_posts'];
        }
        if ( isset( $instance['product_cnt'] ) ){
            $this->iProductsCnt = $instance['product_cnt'];
        }
        if ( isset( $instance['category_ids'] ) ){
            $this->aCategories = explode( ',', $instance['category_ids'] );
        }
        if ( isset( $instance['brand_id'])){
            $this->iBrandId = $instance['brand_id'];
        }

    }
}