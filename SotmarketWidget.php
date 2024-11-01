<?php

class Sotmarket_Widget extends WP_Widget {

    //количество колонок товаров
    protected $iColumnsCnt = 1;

    //заголовок блока по умолчанию
    protected $sTitle = 'Название блока';

    //отображать ли заголовок
    protected $bShowTitle = true;

    //категория из которой нужно выводить товары
    public $aCategories = array();

    //размер картинок
    public $sImageSize = 'default';

    //количество товаров
    public $iProductsCnt = 1;

    //отображать на главной
    protected $bShowOnFrontPage = false;
    protected $aFrontPageProduct = array( 'iProductId' => 0, 'sProductName' => '');
    //отображать в категориях
    protected $bShowOnCategories = false;
    protected $aCategoriesProduct = array( 'iProductId' => 0, 'sProductName' => '');

    //отображать в архиве
    protected $bShowOnArchive = false;
    protected $aArchiveProduct = array( 'iProductId' => 0, 'sProductName' => '');

    //отображать в тегах
    protected $bShowOnTags = false;
    protected $aTagsProduct = array( 'iProductId' => 0, 'sProductName' => '');

    protected $bShowInPosts = false;
    protected $aPostsProduct = array('iProductId' => 0, 'sProductName' => '');

    //показывать только товары в наличии
    protected $bShowOnlyEnabled = true;



    //игнорировать имя и id.
    protected $bIgnoreIdAndName = false;

    //значение subref
    protected $sSubref = '';

    protected $iSiteId;
    protected $oController;
    protected $oImageCache;
    protected $sHomeUrl;


    public function WP_Widget($id_base = false, $name, $widget_options = array(), $control_options = array()){
        parent::WP_Widget(
            $id_base,
            $name,
            $widget_options,
            $control_options
        );
        $this->iSiteId = (int)get_option('sotmarket_site_id');
        $oConfig = new SotmarketConfig(dirname(__FILE__) . "/");
        $oConfig->config['rpc']['site_id'] = $this->iSiteId;

        $callback = new SotmarketRPCClientCallbackImp( $this->iSiteId , session_id());
        $RPC_Client = new SotmarketRPCClient($oConfig->config['rpc'], $callback);
        $this->oController = $RPC_Client->getObjectByClassName('SotmarketRPCOrder');

        $this->oImageCache = new SotmarketClientImageCacheFile($oConfig->config['rpc']);

        $this->sHomeUrl = $this->getSiteUrl();
    }

    /**
     * Фронтенд виджета
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        extract($args);

        //заполняем класс значениями из базы
        $this->setVariables( $instance );

        $title = apply_filters( 'widget_title', $this->sTitle );

        /** @var $before_widget string */
        echo $before_widget;

        try {
            $sProductsView = $this->getProducts();
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case ERROR_STATUS_INFO:
                    return '';
                default:
                    echo $e->getMessage();
            }
        }

        //заголовок показываем если он есть, стоит галка чтобы его показывать и есть контент
        if ( ! empty( $title ) && $this->bShowTitle && $sProductsView) {
            /** @var $before_title string */
            /** @var $after_title string  */
            echo $before_title . $title . $after_title;
        }
        echo $sProductsView;
        /** @var $after_widget string */
        echo $after_widget;
    }

    /**
     * Функция поиска товара по id или имени
     * @param $iProductId - id товара
     * @param string $sProductName - название товара
     * @return array
     */
    protected function getProductIdsByIdAndName( $iProductId = NULL, $sProductName = '', $aCategories = array(11,25)){


        if ( empty($iProductId) && empty($sProductName) && !$this->bIgnoreIdAndName) {
            throw new Exception('нечего искать', ERROR_STATUS_INFO);
        }
        // составляем массив aProductsIds
        if ( !empty( $iProductId )) {
            $aProductIds = array( $iProductId );

            return $aProductIds;
        } else {

            if ( !empty( $sProductName )) {
                try {

                    $aStatuses = $this->bShowOnlyEnabled ? array(1) : array();

                    // сейчас категории жестко прибиты 11,25
                    return $this->oController->product_search_cached($sProductName, $aCategories, $aStatuses );

                } catch (Exception $e) {
                    dumpfile('SotmarketProductSearcher - getProductIdsByIdAndName() :' . $e->getMessage());
                    return array();
                }
            }

        }

        return array();

    }

    /**
     * Получаем ссылку на локальный файл картинки, если она есть в кеше, если нет - сохранаяем
     * в кеш и отдаем
     * @param $iProductId - id товара
     * @param $sImageUrl - путь до картинки на сервере sotmarket
     */
    protected function getCachedImageUrl( $iProductId, $sImageUrl , $sImageSize = 'default' ){

        $sLocalImgUrl = $iProductId . '-' . $sImageSize . $this->oImageCache->sGetExtensionWithDot( $sImageUrl );
        if (!$this->oImageCache->bCheckCache($sLocalImgUrl)) {
            if ($this->oImageCache->bSaveRemote( $sLocalImgUrl, $sImageUrl) ) {
                $sFullImgUrl = $this->oImageCache->sGetImagePath($sLocalImgUrl);
            } else {
                $sFullImgUrl = $this->oImageCache->sGetDefaultImagePath();
            }
        } else {
            $sFullImgUrl = $this->oImageCache->sGetImagePath($sLocalImgUrl);
        }

        return $sFullImgUrl;
    }



    /**
     * Передает переменные в шаблон
     * @param $sViewName
     * @param array $aParams
     */
    protected function render($sViewName,$aParams = array()){

        extract($aParams);

        ob_start();
        include('templates/'. $sViewName .'.php');
        $sContent = ob_get_contents();
        ob_end_clean();

        return $sContent;
    }

    /**
     * Из массива продуктов - делает готовую верстку
     * @param array $aProductsInfo
     */
    protected function renderProductInfo( $aProductsInfo ,$sTemplateName , $sImageSize = 'default' ){

        $iPartnerId = (int)get_option('sotmarket_partner_id');
        $iSiteId = (int)get_option('sotmarket_site_id');
        $sSubref    = get_option('sotmarket_subref_id');
        if ($this->sSubref){
            $sSubref = $this->sSubref;
        }
        $sRefAdd    = '?ref=' . $iPartnerId;

        //собираем переменные для шаблонизатора
        $aParams = array();
        $aParams['iColumnsCnt'] = $this->iColumnsCnt > 0 ? $this->iColumnsCnt : 1;
        $aParams['aProducts'] = array();

        $sLinkType = get_option('sotmarket_linktype_radio');
        foreach( $aProductsInfo as $iProductId => $aProductInfo ){

            $aProduct = array(
                'id' => $iProductId,
                'sale' => $aProductInfo['isSale'] ? 'СКИДКА&nbsp;' : '',
                'title' => $aProductInfo['name'],
                'old_price' => $aProductInfo['old_price'],
                'price'     => $aProductInfo['price'],
                'image_src' => ''
            );

            //проверяем настройки диплинков
            $sCpaLinkStart = get_option('sotmarket_cpa_link_begin');
            $sCpaLinkEnd = get_option('sotmarket_cpa_link_end');
            $sCpaLinkGet = get_option('sotmarket_cpa_link_get');

            //формируем ссылку
            if ($sCpaLinkStart || $sCpaLinkEnd){
                $aProduct['url'] = $sCpaLinkStart . $aProductInfo['info_url'] . $sCpaLinkEnd;
            } elseif ($sCpaLinkGet){
                $aProduct['url'] = $aProductInfo['info_url'] .'?'. $sCpaLinkGet;
            } else {
                $aProduct['url'] = $aProductInfo['info_url'] . $sRefAdd;
                $aProduct['url'] .= '&subref=wp.'.$iSiteId.'.'.$this->sType.'.'.$iProductId;
                if ( $sSubref ){
                    $aProduct['url'] .= '.any_'.$sSubref;
                }
            }

            if ($sLinkType == 'internal'){
                $sSecret = md5($sLinkType.$iPartnerId.$this->iSiteId);

                $aProduct['url'] = base64_encode( sotm_encode($sSecret.$aProduct['url'],$sSecret));
                $aProduct['url'] = $this->getSiteUrl() . '?srdr=' . $aProduct['url'];
            }

            //получаем картинку для каждого товара и помещаем её в кеш
            $sImgUrl             = $aProductInfo['image_url'];
            $sFullImgUrl = $this->getCachedImageUrl( $iProductId , $sImgUrl , $sImageSize );
            $aProduct['image_src'] = $this->getSiteUrl() . $sFullImgUrl;

            $aParams['aProducts'][] = $aProduct;
        }

        //собрали переменные - передаем в шаблонизатор
        $sReturn = $this->render( $sTemplateName, $aParams );

        return $sReturn;
    }

    /**
     * Возвращает полную ссылку на сайт
     * @return string
     */
    protected function getSiteUrl(){

        if (defined('WP_SITEURL')){
            return WP_SITEURL;
        }else {
            return home_url().'/';
        }
    }

    /**
     * устанавливает переменные класса значениями из базы
     * @param $instance
     */
    protected function setVariables( $instance ){

        if ( isset( $instance['columns_cnt'] ) ){
            $this->iColumnsCnt = $instance['columns_cnt'];
        }
        if ( isset( $instance['title']) ){
            $this->sTitle = $instance['title'];
        }
        if ( isset( $instance['show_title']) ){
            $this->bShowTitle = $instance['show_title'];
        }
        if ( isset( $instance['show_on_front']) ){
            $this->bShowOnFrontPage = $instance['show_on_front'];
            $this->aFrontPageProduct['iProductId'] = isset($instance['front_product_id']) ? $instance['front_product_id'] : 0;
            $this->aFrontPageProduct['sProductName'] = isset($instance['front_product_name']) ? $instance['front_product_name'] : '';
        }
        if ( isset( $instance['show_on_categories'] ) ){
            $this->bShowOnCategories = $instance['show_on_categories'];
            $this->aCategoriesProduct['iProductId'] = isset($instance['categories_product_id']) ? $instance['categories_product_id'] : 0;
            $this->aCategoriesProduct['sProductName'] = isset($instance['categories_product_name']) ? $instance['categories_product_name'] : '';
        }
        if ( isset( $instance['show_on_archive'] ) ){
            $this->bShowOnArchive = $instance['show_on_archive'];
            $this->aArchiveProduct['iProductId'] = isset($instance['archive_product_id']) ? $instance['archive_product_id'] : 0;
            $this->aArchiveProduct['sProductName'] = isset($instance['archive_product_name']) ? $instance['archive_product_name'] : '';
        }
        if ( isset( $instance['show_on_tags'] ) ){
            $this->bShowOnTags = $instance['show_on_tags'];
            $this->aTagsProduct['iProductId'] = isset($instance['tags_product_id']) ? $instance['tags_product_id'] : 0;
            $this->aTagsProduct['sProductName'] = isset($instance['tags_product_name']) ? $instance['tags_product_name'] : '';
        }
        if ( isset( $instance['show_on_posts'] ) ){
            $this->bShowInPosts = $instance['show_on_posts'];
            $this->aPostsProduct['iProductId'] = isset($instance['posts_product_id']) ? $instance['posts_product_id'] : 0;
            $this->aPostsProduct['sProductName'] = isset($instance['posts_product_name']) ? $instance['posts_product_name'] : '';
        }
        if ( isset( $instance['show_only_enabled'] ) ){
            $this->bShowOnlyEnabled = $instance['show_only_enabled'];
        }
        if ( isset( $instance['image_size']) ){
            $this->sImageSize = $instance['image_size'];
        }

        if ( isset( $instance['subref']) ){
            $this->sSubref = $instance['subref'];
        }
    }

    public function commonForm( ){

        ?>
    <p>
        <label for="title">Заголовок блока:</label><br/>
        <input type="text" id="title"
               name="<?= $this->get_field_name('title') ?>" value="<?= trim($this->sTitle) ?>" /><br/>
        <input type="checkbox" id="show_title"
               name="<?= $this->get_field_name('show_title') ?> " <?= $this->bShowTitle ? 'checked' : '' ?> >
        <label for="show_title">Показывать заголовок</label>
    </p>
    <hr>
    <p>
        Активирование настроек ниже приведет отключению виджета на страницах использующих ID или название с произвольного поля
    </p>
    <p>
        <input type="checkbox" id="show_on_front"
               name="<?= $this->get_field_name('show_on_front') ?>" <?= $this->bShowOnFrontPage ? 'checked' : '' ?> >
        <label for="show_on_front"> Показывать на главной</label><br/>
        <label for="front_product_id">Id товара или товаров через "," на главной:</label> <br/>
        <input type="text" id="front_product_id" style="width: 100%;"
               name="<?= $this->get_field_name('front_product_id') ?>" value="<?= $this->aFrontPageProduct['iProductId'] ?>" /><br/>
        <label for="front_product_name">имя товара на главной:</label> <br/>
        <input type="text" id="front_product_name"
               name="<?= $this->get_field_name('front_product_name') ?>" value="<?= $this->aFrontPageProduct['sProductName'] ?>" /><br/>

    </p>
    <p>
        <input type="checkbox" id="show_on_categories"
               name="<?= $this->get_field_name('show_on_categories') ?>" <?= $this->bShowOnCategories ? 'checked' : '' ?> >
        <label for="show_on_categories"> Показывать в категориях</label><br/>
        <label for="categories_product_id">Id товара или товаров через "," в категориях:</label><br/>
        <input type="text" id="categories_product_id" style="width: 100%;"
               name="<?= $this->get_field_name('categories_product_id') ?>" value="<?= $this->aCategoriesProduct['iProductId'] ?>" /><br/>
        <label for="categories_product_name">имя товара в категориях:</label> <br/>
        <input type="text" id="categories_product_name"
               name="<?= $this->get_field_name('categories_product_name') ?>" value="<?= $this->aCategoriesProduct['sProductName'] ?>" /><br/>

    </p>
    <p>
        <input type="checkbox" id="show_on_archive"
               name="<?= $this->get_field_name('show_on_archive') ?>" <?= $this->bShowOnArchive ? 'checked' : '' ?> >
        <label for="show_on_categories"> Показывать в архивах</label><br/>
        <label for="categories_product_id">Id товара или товаров через "," в архивах:</label><br/>
        <input type="text" id="archive_product_id" style="width: 100%;"
               name="<?= $this->get_field_name('archive_product_id') ?>" value="<?= $this->aArchiveProduct['iProductId'] ?>" /><br/>
        <label for="categories_product_name">имя товара в архивах:</label> <br/>
        <input type="text" id="archive_product_name"
               name="<?= $this->get_field_name('archive_product_name') ?>" value="<?= $this->aArchiveProduct['sProductName'] ?>" /><br/>

    </p>
    <p>
        <input type="checkbox" id="show_on_tags"
               name="<?= $this->get_field_name('show_on_tags') ?>" <?= $this->bShowOnTags ? 'checked' : '' ?> >
        <label for="show_on_categories"> Показывать в тэгах</label><br/>
        <label for="tags_product_id">Id товара или товаров через "," в тэгах:</label><br/>
        <input type="text" id="tags_product_id" style="width: 100%;"
               name="<?= $this->get_field_name('tags_product_id') ?>" value="<?= $this->aTagsProduct['iProductId'] ?>" /><br/>
        <label for="tags_product_name">имя товара в тегах:</label> <br/>
        <input type="text" id="tags_product_name"
               name="<?= $this->get_field_name('tags_product_name') ?>" value="<?= $this->aTagsProduct['sProductName'] ?>" /><br/>

    </p>
    <p>
        <input type="checkbox" id="show_on_posts"
               name="<?= $this->get_field_name('show_on_posts') ?>" <?= $this->bShowInPosts ? 'checked' : '' ?> >
        <label for="show_on_posts"> Показывать в полной новости</label><br/>
        <label for="posts_product_id">Id товара или товаров через "," в новости:</label><br/>
        <input type="text" id="posts_product_id" style="width: 100%;"
               name="<?= $this->get_field_name('posts_product_id') ?>" value="<?= $this->aPostsProduct['iProductId'] ?>" /><br/>
        <label for="tags_product_name">имя товара в новости:</label> <br/>
        <input type="text" id="posts_product_name"
               name="<?= $this->get_field_name('posts_product_name') ?>" value="<?= $this->aPostsProduct['sProductName'] ?>" /><br/>

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


    <?
    }

    protected function getProductIdName($post){

        if ((is_home() || is_front_page())&& $this->bShowOnFrontPage) {
            //если указано что нужно отображать на главной
            $iProductId = $this->aFrontPageProduct['iProductId'];
            $sProductName = $this->aFrontPageProduct['sProductName'];
        } elseif ( is_category() && $this->bShowOnCategories ) {
            //если указано что нужно выводить в категориях
            $iProductId = $this->aCategoriesProduct['iProductId'];
            $sProductName = $this->aCategoriesProduct['sProductName'];
        } elseif ( is_archive() && $this->bShowOnArchive){
            //если архив
            $iProductId = $this->aArchiveProduct['iProductId'];
            $sProductName = $this->aArchiveProduct['sProductName'];
        } elseif ( is_tag() && $this->bShowOnTags ){
            //в тегах
            $iProductId = $this->aTagsProduct['iProductId'];
            $sProductName = $this->aTagsProduct['sProductName'];
        } elseif (isset($post->ID) && !is_home() && !is_category() && !is_archive() && !is_tag() && $this->bShowInPosts) {
            $iProductId = $this->aPostsProduct['iProductId'];
            $sProductName = $this->aPostsProduct['sProductName'];
        } elseif (isset($post->ID)
            && !is_home() && !is_category() && !is_archive() && !is_tag()
            && !$this->bShowOnFrontPage && !$this->bShowOnCategories &&
            !$this->bShowOnArchive && !$this->bShowOnTags
        ){
            //если указан товар
            $iProductId = get_post_meta($post->ID, 'sotm_product_id', true);
            $sProductName = get_post_meta($post->ID, 'sotm_product_name', true);
        } else {
            return array();
        }

        return array(
            'iProductId' => $iProductId,
            'sProductName' => $sProductName,
        );
    }



}