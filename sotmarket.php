<?php
/**
Plugin Name: Sotmarket WP
Plugin URI: http://forum.sotmarket.ru/index.php?/forum/105-plaginy-dlya-wordpress/
Description: Работа с партнерским магазином sotmarket.ru
Version: 3.0.9
Author URI: http://www.sotmarket.ru
Forum URI: http://forum.sotmarket.ru/index.php?/forum/105-plaginy-dlya-wordpress/
 **/
require_once(ABSPATH . '/wp-content/plugins/sotmarket-affiliate-plugin/include.php');

//подключаем виджеты
require_once dirname(__FILE__) . '/SotmarketAdminTab.php';
require_once dirname(__FILE__) . '/SotmarketWidget.php';
require_once dirname(__FILE__) . '/SotmarketInfoWidget.php';
require_once dirname(__FILE__) . '/SotmarketAnalogWidget.php';
require_once dirname(__FILE__) . '/SotmarketRelatedWidget.php';
require_once dirname(__FILE__) . '/SotmarketPopularWidget.php';

//подключаем функции вставки инфоблока php функциями
require_once dirname(__FILE__) . '/code_insert_functions.php';

add_action('widgets_init', create_function('', 'return register_widget("Sotmarket_Info_Widget");'));
add_action('widgets_init', create_function('', 'return register_widget("Sotmarket_Related_Widget");'));
add_action('widgets_init', create_function('', 'return register_widget("Sotmarket_Analog_Widget");'));
add_action('widgets_init', create_function('', 'return register_widget("Sotmarket_Popular_Widget");'));

add_action('init', 'sotmarket_session', 1);

if (!function_exists('sotmarket_session')) {
    function sotmarket_session() {
        if (!session_id()) {
            session_start();
        }
    }
}

//проверка если в плагин пришла зашифрованная ссылка для редиректа
if (isset($_GET['srdr']) && get_option('sotmarket_linktype_radio') == 'internal'){

    $sSecret = md5(get_option('sotmarket_linktype_radio').get_option('sotmarket_partner_id').get_option('sotmarket_site_id'));
    $sUrl = $_GET['srdr'];
    $sUrl = base64_decode($sUrl);
    $sUrl = sotm_decode($sUrl,$sSecret);
    //validate url
    if (strstr($sUrl,$sSecret) == false){
        header('Location: /');
        exit;
    } else {
        $sUrl = substr($sUrl,strlen($sSecret));
        header( 'Location: '.$sUrl);
        exit;
    }


}


if (is_admin()) {
    /* activations */
    register_activation_hook(__FILE__, 'sotmarket_install');
    /*deactivation*/
    register_deactivation_hook(__FILE__, 'sotmarket_uninstall');
}

function sotmarket_uninstall(){

    $bIsInstalled = get_option('sotmarket_site_id');
    if ($bIsInstalled){
        delete_option('sotmarket_site_id');
    }
}

function sotmarket_install() {
    global $wpdb, $wp_rewrite;
    $installed = get_option('sotmarket_site_id');

    if (empty($installed)) {
        // непонятно по каким причинам объекта $wp_rewrite нет, создаем его
        if (!isset($wp_rewrite)) $wp_rewrite =& new WP_Rewrite();
        // добавляем страницу для оформления заказа
        $default_option['sotmarket_ordertype_radio'] = 'link';

        $default_option['sotmarket_linktype_radio'] = 'external';

        //$default_option['sotmarket_type_radio'] = 'both';
        $wpdb->query('INSERT INTO ' . $wpdb->prefix . 'postmeta (post_id, meta_key) VALUES (0, \'sotm_product_id\'), (0, \'sotm_product_name\') ');

        foreach ($default_option as $sOptionName => $sOptionValue) {
            update_option($sOptionName, $sOptionValue);
        }

        chmod( dirname(__FILE__) . '/_data',0777);
        if (!is_dir(dirname(__FILE__) . '/_data/tmp')){
            mkdir(dirname(__FILE__) . '/_data/tmp',0755);
        }

        $handle = opendir( dirname(__FILE__) . '/templates' );
        while( $sFileName = readdir($handle) ) {
            if ( $sFileName != "." && $sFileName != ".." ) {
                chmod( dirname(__FILE__) . '/templates/' .$sFileName, 0755 );
            }
        }
        closedir($handle);


    }
}

add_action('admin_menu', 'sotmarket_wp_add_pages');
$oSotmarketWP = new Sotmarket_Info_Widget();
add_filter('the_content', array($oSotmarketWP, 'sotmarket_checkout'));
$oSotmarketAnalogWidget = new Sotmarket_Analog_Widget();
add_filter('the_content', array( $oSotmarketAnalogWidget,'sotmarket_checkout'));
$oSotmarketRelatedWidget = new Sotmarket_Related_Widget();
add_filter('the_content', array( $oSotmarketRelatedWidget,'sotmarket_checkout'));
$oSotmarketPopularWidget = new Sotmarket_Popular_Widget();
add_filter('the_content', array( $oSotmarketPopularWidget,'sotmarket_checkout'));


// action function for above hook
function sotmarket_wp_add_pages() {
    add_options_page('Sotmarket WP', 'Sotmarket WP', 'administrator', 'sotmarket_wp', 'sotmarket_wp_options_page');
}

function sotmarket_wp_options_page() {
    //урл до настроек
    $sSotmarketPluginUrl = '/wp-admin/options-general.php?page=sotmarket_wp';

    $sServerIp = $_SERVER['SERVER_ADDR'];
    echo '<br/><b>IP вашего сайта:</b> '.$sServerIp;

    $aAllOptions  = array(
        'sotmarket_site_id'             => 'ID сайта *',
        'sotmarket_partner_id'          => 'ID сети *',
        'sotmarket_subref_id'           => 'Метка subref ( при использовании <a href="http://forum.sotmarket.ru/index.php?/topic/1373-kak-pravilno-nastroit-diplinki-i-get-ssilki-pri/">диплинков</a> - не заполняем )',
        'sotmarket_linktype_radio'      => 'Тип ссылки на товар: # Прямая ссылка на товар (external)* Внутренняя ссылка с редиректом (internal)',
        'sotmarket_viewavailable_radio' => 'Для тега показывать только: # Товары всех статусов (all)* Товары в наличии (available)',
    );
    $aOptionNames = array_keys($aAllOptions);
    $aMainOptionsName = $aOptionNames;
    $aCpaOptions = array('sotmarket_cpa_link_begin','sotmarket_cpa_link_end','sotmarket_cpa_link_get');
    $aOptionNames = array_merge($aOptionNames,$aCpaOptions);
    foreach ($aOptionNames as $sOptionName) {
        $aOption[$sOptionName] = stripslashes(get_option($sOptionName));
    }

    $sHeader     = __('Options saved.', SOTMARKET_TRANS_DOMAIN);
    $sHeader2    = __('Sotmarket WP Plugin Options', SOTMARKET_TRANS_DOMAIN);

    //получаем названия шаблонов
    $aTemplateFiles = glob(dirname(__FILE__) .'/templates/*.php');
    $aTemplateFiles = str_replace( array(dirname(__FILE__) .'/templates/','.php'),'',$aTemplateFiles );

    //отображаем редактирование шаблона
    if ($_GET['template'] ){
        $sTemplateName = $_GET['template'];
        if (!in_array($sTemplateName,$aTemplateFiles)){
            echo 'Шаблон не найден';
            return;
        }


        $sTemplateContent = htmlspecialchars(file_get_contents( dirname(__FILE__) . '/templates/' . $sTemplateName . '.php'), ENT_QUOTES);
        echo <<<END
			<h3>Редактирование шаблона</h3>
			<a href="$sSotmarketPluginUrl">вернуться к настройкам</a>
        	<form method="post" action="$sSotmarketPluginUrl">
                <input type="hidden" name="hidden_send_template" value="Y">
                <input type="hidden" name="hidden_template_name" value="$sTemplateName" >
END;
        if (isset($_GET['new'])){
            echo '<label>Название (в одно слово, латиницей)</label><br/>';
            echo '<input style="width:400px" name="new_template_name" value="'. $sTemplateName . 'custom"><br/>';
        }
        echo <<<END
                <label>Шаблон:</label><br/>
                <textarea rows="10" cols="120" name="template-code">$sTemplateContent</textarea>
                <p class="submit">
					<input type="submit" name="Submit" value="Сохранить" />
				</p>
			</form>
			<hr />
			<h3>Описание переменных</h3>
			<div>
				<p>iColumnsCnt - Устанавливается в настройках виджета</p>
				<p>aProducts - Массив с данными о товарах</p>
				<p>Элемент массива данными следующий:<br/>
					array(  <br/>
		            	'url' - Ссылка на страницу с описанием товара на сайте sotmarket.ru с сохранением REF ссылки. <br/>
		            	'title' - название товара <br/>
		            	'image_src' - Путь к изображению товара <br/>
		            	'price' - цена товара <br/>
		            	'old_price' - Цена до распродажи <br/>
		                'sale' - текст SALE, в этой переменной только если по товару идет распродажа<br/>
                    )

				</p>
			</div>
			<div>
				<a href="$sSotmarketPluginUrl&template=$sTemplateName&new=1">Сделать новый на основе этого</a>
			</div>
END;
        return;


    }

    //проверяем посылку запроса на изменение шаблона
    if ($_POST['hidden_send_template'] == 'Y') {

        if ( isset( $_POST['new_template_name'] ) ){
            $sTemplateName = $_POST['new_template_name'];
        } else {
            $sTemplateName = $_POST['hidden_template_name'];
        }

        $sTemplateContent = $_POST['template-code'];
        //зачем то wp экранирует символы
        $sTemplateContent = str_replace('\"','"',$sTemplateContent);
        $sTemplateContent = str_replace("\'","'",$sTemplateContent);

        file_put_contents(dirname(__FILE__) . '/templates/' . $sTemplateName . '.php',$sTemplateContent);
        echo '<div class="updated"><p><strong>Шаблон обновлен</strong></p></div>';
    }

    // Проверяем что была посылка запроса
    if ($_POST['hidden_send'] == 'Y') {
        foreach ($aOptionNames as $sOptionName) {
            if (!isset($_POST[$sOptionName])) $_POST[$sOptionName] = '';
            $_POST[$sOptionName] = stripslashes($_POST[$sOptionName]);
            update_option($sOptionName, $_POST[$sOptionName]);
            $aOption[$sOptionName] = $_POST[$sOptionName];
        }
        foreach($aCpaOptions as $sOptionName){
            if (!isset($_POST[$sOptionName])) $_POST[$sOptionName] = '';
            $_POST[$sOptionName] = stripslashes($_POST[$sOptionName]);
            update_option($sOptionName, $_POST[$sOptionName]);
            $aOption[$sOptionName] = $_POST[$sOptionName];
        }

        echo '<div class="updated"><p><strong>' . $sHeader . '</strong></p></div>';
    }
    ECHO <<<END

<div class="wrap">
  <h2>$sHeader2</h2>
<form name="form1" method="post" action="">
    <input type="hidden" name="hidden_send" value="Y">
END;
    foreach ($aMainOptionsName as $sOptionName) {

        if (strpos($sOptionName, 'radio') !== false) {
            $sTitleVariants = __($aAllOptions[$sOptionName]);
            preg_match('!(.*)#(.*)!', $sTitleVariants, $aMatches);
            $sVariants = $aMatches[2];
            $sTitle    = $aMatches[1];
            $aVariants = preg_split('!\*!', $sVariants);
            echo "<p>$sTitle<br />";
            foreach ($aVariants as $sVariant) {
                preg_match('!(.*)\((.*)\)!', $sVariant, $aFound);
                $sVarTitle = $aFound[1];
                $sVarValue = $aFound[2];
                echo "<input" . (($sVarValue == $aOption[$sOptionName]) ? " checked='true'"
                    : "") . " type='radio' name='$sOptionName' value='$sVarValue'>&nbsp;$sVarTitle<br />";
            }
            echo "</p><hr />";
        } else {
            echo "<p>" . __($aAllOptions[$sOptionName], SOTMARKET_TRANS_DOMAIN) . ":<br /><input type='text' name='$sOptionName' value='$aOption[$sOptionName]'></p><hr />";
        }
    }
    wp_enqueue_script('postbox');
    wp_enqueue_script('dashboard');
    wp_enqueue_script( 'sotmarket-options', plugins_url( 'js/sotm_options.js',    __FILE__ ), array( 'jquery' ) );
    echo '
<div class="postbox closed">
<h3 class="hndle" style="cursor:pointer;margin: 10px 0 0 10px;padding: 0 0 10px 0;"><span>Настройки диплинков ( используется только при работе с <a href="http://forum.sotmarket.ru/index.php?/topic/137-sotrudnichestvo-s-offerom-sotmarket-cherez-cpa-seti/">CPA сетями</a> )</span></h3>
<div class="inside" >
    <p>Начало диплинка:<br />

        <input type="text" id="SOTMARKET_CPA_LINK_BEGIN" style="width: 400px;" name="sotmarket_cpa_link_begin" value="'.$aOption['sotmarket_cpa_link_begin'].'">
    </p><hr />
    <p>Конец диплинка:<br />
        <input type="text" id="SOTMARKET_CPA_LINK_END" style="width: 400px;" name="sotmarket_cpa_link_end" value="'.$aOption['sotmarket_cpa_link_end'].'">
    </p><hr />
    <p>Параметр GET ссылки:<br />
        <input type="text" id="SOTMARKET_CPA_LINK_GET" style="width: 400px;" name="sotmarket_cpa_link_get" value="'.$aOption['sotmarket_cpa_link_get'].'">
    </p><hr />
    <p>Пример ссылки:<br/>';
    if ($aOption['sotmarket_cpa_link_begin'] || $aOption['sotmarket_cpa_link_end'] ){
        echo '<div id="link_test">'. @$aOption['sotmarket_cpa_link_begin'] . 'http://www.sotmarket.ru/' . @$aOption['sotmarket_cpa_link_end'] .'</div>';
    } elseif( $aOption['sotmarket_cpa_link_get'] ) {
        echo '<div id="link_test">http://www.sotmarket.ru/?' . $aOption['sotmarket_cpa_link_get'] .'</div>';
    } else {
        echo '<div id="link_test"></div>';
    }
    echo '</p>
</div>
</div>';
echo <<<END
<p class="submit">
<input type="submit" name="Submit" value="Сохранить" />
</p><hr />
<div class="postbox closed">
<h3 class="hndle" style="cursor:pointer;margin: 10px 0 0 10px;padding: 0 0 10px 0;"><span>Редактировать шаблоны</span></h3>
<div class="inside" >

END;
    //выводим все файлы на редактирование
    foreach( $aTemplateFiles as $sTemplate ){
        echo '<a href="' . $sSotmarketPluginUrl. '&template=' . $sTemplate . '">' . $sTemplate . '</a><br/>';
    }
    ECHO <<<END
</div>
</div>
<hr />
<h3>Как получить ID своего сайта:</h3>
<strong>От партнера требуется:</strong><br />
1. Скачать плагин и установить согласно инструкции;<br />
2. Написать нам ( ICQ: 751017, 444531; filipp.s@sotmarket.ru );<br />
3. Сообщить название CPA сети* ( или свой партнерский ID если Вы работаете напрямую с нами ), исходящий IP адрес сайта, домен на котором установлен плагин;<br />
4. Получить от нас ID сайта и использовать;<br />
5. Обязательно сделать настройку диплинов при работе в CPA сетях. ( <a href="http://forum.sotmarket.ru/index.php?/topic/1373-kak-pravilno-nastroit-diplinki-i-get-ssilki-pri/">Как правильно настроить диплинк</a> - читаем на форуме поддержки. );<br /><br />
* CPA сети - подробная информация на нашем <a href="http://forum.sotmarket.ru/index.php?/forum/115-partnerskie-seti/">форуме поддержки плагина</a>
 <hr />
 <h3>Так же вы можете вывести блок php-кодом в шаблоне</h3>
 <p>getSotmarketInfo( \$sTemplateName = 'sotmarket_info_base', \$iCnt = 1, \$sImageSize='default',\$aCategories = array(), \$aProductsId = array() ); -
 Вывод товаров<br/>
    \$sTemplateName - название шаблона<br/>
    \$iCnt - количество товара <br/>
    \$sImageSize - тип картинки <br/>
    \$aCategories массив каталогов в которых искать товар <br/>
    \$aProductsId - массив заданных id товаров

 </p>
 <p>getSotmarketAnalog(\$sTemplateName = 'sotmarket_info_base', \$iCnt = 1, \$sImageSize='default',\$aCategories = array(), \$aProductsId = array() ); -
 Вывод аналогичных товаров<br/>
   \$sTemplateName - название шаблона<br/>
    \$iCnt - количество товара <br/>
    \$sImageSize - тип картинки <br/>
    \$aCategories массив каталогов в которых искать товар <br/>
    \$aProductsId - массив заданных id товаров
 </p>
 <p>getSotmarketRelated( \$sTemplateName = 'sotmarket_info_base', \$iCnt = 1, \$sImageSize='default',\$aCategories = array(), \$aProductsId = array()); -
 Вывод сопутствующих товаров<br/>
   \$sTemplateName - название шаблона<br/>
    \$iCnt - количество товара <br/>
    \$sImageSize - тип картинки <br/>
    \$aCategories массив каталогов в которых искать товар <br/>
    \$aProductsId - массив заданных id товаров
 </p>
 <p>getSotmarketPopular( \$sTemplateName = 'sotmarket_info_base', \$iCnt = 1, \$sImageSize='default',\$aCategories = array(), \$iBrandId = 0); -
 Вывод сопутствующих товаров<br/>
   \$sTemplateName - название шаблона<br/>
    \$iCnt - количество товара <br/>
    \$sImageSize - тип картинки <br/>
    \$aCategories массив каталогов в которых искать товар <br/>
    \$iBrandId id бренда
 </p>
 <p>
    <strong>Примеры вставки: </strong><br/>
    echo getSotmarketInfo('sotmarket_info_base',2,'default',array(),array('456949')); <br/>
    echo getSotmarketAnalog('sotmarket_analog_base',2,'default',array(),array('456949')); <br/>
    echo getSotmarketRelated('sotmarket_related_base',2,'default',array(),array('456949')); <br/>
    echo getSotmarketPopular('sotmarket_popular_base',5,'default',array(),14); <br/>

 </p>
</form>
</div>
END;
}

?>