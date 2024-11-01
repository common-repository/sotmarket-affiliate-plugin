<?php


/**
 * Класс для отображения метабокса при редактировании постов
 */
class SotmarketAdminTab {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );
		//add_action('save_post', array($this, 'updateMetaBox'), 0);

		wp_enqueue_style(  'jf-metabox-tabs', plugins_url( 'css/metabox-tabs.css',   __FILE__ ) );
		wp_enqueue_script( 'jf-metabox-tabs', plugins_url( 'js/metabox-tabs.js',    __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'sotmarlet-admin-tab', plugins_url( 'js/sotmarket-admin-tab.js',    __FILE__ ), array( 'jquery' ) );
	}

	public function addMetaBox() {
		add_meta_box( 'sotmarket_extra_fields', 'Sotmarket теги', array( $this, 'viewMetaBox' ), 'post','side');
		add_meta_box( 'sotmarket_extra_fields', 'Sotmarket теги', array( $this, 'viewMetaBox' ), 'page','side');
	}

	public function viewMetaBox( $post ) {
		?>

	<div class="metabox-tabs-div">
		<ul class="metabox-tabs" id="metabox-tabs">
			<li class="active tab1"><a class="active" href="javascript:void(null);">Товары</a></li>
			<li class="tab2"><a href="javascript:void(null);">Аналоги</a></li>
			<li class="tab3"><a href="javascript:void(null);">Сопут.товары</a></li>
            <li class="tab4"><a href="javascript:void(null);">Поп.товары</a></li>
		</ul>
		<div class="tab1">
			<h4 class="heading">Товары</h4>
			<div>
				<h4>Генерация тега</h4>
				<p>

					<label>Какой шаблон использовать:</label><br/>
					<select id="sotmarket-info-template">
						<?= $this->getFiles('sotmarket_info'); ?>
					</select>
				</p>
				<p>
					<label>Выводить товары только</label><br/>
					<select id="sotmarket-info-viewtype">
						<option value="both" selected>Везде</option>
						<option value="announce">В анонсе</option>
						<option value="full">В полной новости</option>
					</select>
				</p>
				<p>
					<label>Размер картинки:</label><br/>
					<select id="sotmarket-info-image-size">
						<option value="default" >Стандартные</option>
						<option value="100x100">100x100</option>
						<option value="140x200">140x200</option>
						<!--<option value="300x250">300x250</option>-->
						<option value="1200x1200">1200x1200</option>
						<option value="100x150">100x150</option>
						<option value="50x50">50x50</option>
					</select>
				</p>
                <p>
                    <label>id товаров (через запятую)</label>
                    <input type="text" id="sotmarket-info-ids" value="" />
                </p>
                <p>
                    <label>значение subref</label><br/>
                    <input type="text" id="sotmarket-info-subref" value="" />
                </p>
				<p>
					<label>Полученый тег:</label><br/>
					<span id="sotmarket-info-tag">[sotmarket_info_base]</span>
				</p>
			</div>
		</div>
		<div class="tab2">
			<h4 class="heading">Аналоги</h4>
			<div>
				<h4>Генерация тега</h4>
				<p>
					<label>Какой шаблон использовать:</label><br/>
					<select id="sotmarket-analog-template">
						<?= $this->getFiles('sotmarket_analog'); ?>

					</select>
				</p>
				<p>
					<label>Сколько товаров выводить:</label><br/>
					<input type="text" id="sotmarket-analog-cnt" value="1" />
				</p>
				<p>
					<label>Выводить товары только</label><br/>
					<select id="sotmarket-analog-viewtype">
						<option value="both" selected>Везде</option>
						<option value="announce">В анонсе</option>
						<option value="full">В полной новости</option>
					</select>
				</p>
				<p>
					<label>Размер картинки:</label><br/>
					<select id="sotmarket-analog-image-size">
						<option value="default" >Стандартные</option>
						<option value="100x100">100x100</option>
						<option value="140x200">140x200</option>
						<!--<option value="300x250">300x250</option>-->
						<option value="1200x1200">1200x1200</option>
						<option value="100x150">100x150</option>
						<option value="50x50">50x50</option>
					</select>
				</p>
                <p>
                    <label>id товаров (через запятую)</label>
                    <input type="text" id="sotmarket-analog-ids" value="" />
                </p>
                <p>
                    <label>значение subref:   </label><br/>
                    <input type="text" id="sotmarket-analog-subref" value="" />
                </p>
				<p>
					<label>Полученый тег:</label><br/>
					<span id="sotmarket-analog-tag">[sotmarket_analog_base]</span>
				</p>
			</div>
		</div>
		<div class="tab3">
			<h4 class="heading">Сопуствующие</h4>
			<div>
				<h4>Генерация тега</h4>
				<p>
					<label>Какой шаблон использовать:</label><br/>
					<select id="sotmarket-related-template">
						<?= $this->getFiles('sotmarket_related'); ?>

					</select>
				</p>
				<p>
					<label>Сколько товаров выводить:</label><br/>
					<input type="text" id="sotmarket-related-cnt" value="1" />
				</p>
				<p>
					<label>Выводить товары только</label><br/>
					<select id="sotmarket-related-viewtype">
						<option value="both" selected>Везде</option>
						<option value="announce">В анонсе</option>
						<option value="full">В полной новости</option>
					</select>
				</p>
				<p>
					<label>Id категорий через запятую:</label><br/>
					<input type="text" id="sotmarket-related-cats" value="" />
				</p>
				<p>
					<label>Размер картинки:</label><br/>
					<select id="sotmarket-related-image-size">
						<option value="default" >Стандартные</option>
						<option value="100x100">100x100</option>
						<option value="140x200">140x200</option>
						<!--<option value="300x250">300x250</option>-->
						<option value="1200x1200">1200x1200</option>
						<option value="100x150">100x150</option>
						<option value="50x50">50x50</option>
					</select>
				</p>
                <p>
                    <label>id товаров (через запятую)</label>
                    <input type="text" id="sotmarket-related-ids" value="" />
                </p>
                <p>
                    <label>значение subref</label><br/>
                    <input type="text" id="sotmarket-related-subref" value="" />
                </p>
				<p>
					<label>Полученый тег:</label><br/>
					<span id="sotmarket-related-tag">[sotmarket_related_base]</span>
				</p>
			</div>
		</div>
        <div class="tab4">
            <h4 class="heading">Популярные</h4>
            <div>
                <h4>Генерация тега</h4>
                <p>
                    <label>Какой шаблон использовать:</label><br/>
                    <select id="sotmarket-popular-template">
                        <?= $this->getFiles('sotmarket_popular'); ?>

                    </select>
                </p>
                <p>
                    <label>Сколько товаров выводить:</label><br/>
                    <input type="text" id="sotmarket-popular-cnt" value="1" />
                </p>
                <p>
                    <label>Выводить товары только</label><br/>
                    <select id="sotmarket-popular-viewtype">
                        <option value="both" selected>Везде</option>
                        <option value="announce">В анонсе</option>
                        <option value="full">В полной новости</option>
                    </select>
                </p>
                <p>
                    <label>Id категорий через запятую:</label><br/>
                    <input type="text" id="sotmarket-popular-cats" value="" />
                </p>
                <p>
                    <label>Размер картинки:</label><br/>
                    <select id="sotmarket-popular-image-size">
                        <option value="default" >Стандартные</option>
                        <option value="100x100">100x100</option>
                        <option value="140x200">140x200</option>
                        <!--<option value="300x250">300x250</option>-->
                        <option value="1200x1200">1200x1200</option>
                        <option value="100x150">100x150</option>
                        <option value="50x50">50x50</option>
                    </select>
                </p>
                <p>
                    <label>id бренда</label><br/>
                    <input type="text" id="sotmarket-popular-brand-id" value="" />
                </p>
                <p>
                    <label>значение subref</label><br/>
                    <input type="text" id="sotmarket-popular-subref" value="" />
                </p>
                <p>
                    <label>Полученый тег:</label><br/>
                    <span id="sotmarket-popular-tag">[sotmarket_popular_base]</span>
                </p>
            </div>
        </div>
		<input type="hidden" name="sotmarket_extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	</div>
	<?php
	}

	public function updateMetaBox( $post_id ){
		if ( !wp_verify_nonce($_POST['sotmarket_extra_fields_nonce'], __FILE__) ) return false; // проверка
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false; // выходим если это автосохранение
		if ( !current_user_can('edit_post', $post_id) ) return false; // выходим если юзер не имеет право редактировать запись

		if( !isset($_POST['sotmarket_extra']) ) return false; // выходим если данных нет

		// Все ОК! Теперь, нужно сохранить/удалить данные
		$aPostData = $_POST['sotmarket_extra'];
		$aPostData = array_map('trim', $aPostData ); // чистим все данные от пробелов по краям
		foreach( $aPostData as $key=>$value ){
			if( empty($value) ){
				delete_post_meta($post_id, $key);
				continue;
			}

			update_post_meta($post_id, $key, $value);
		}
		return $post_id;
	}


	protected function getFiles( $sWidgetName ){

		//папка с темплейтами
		$sTemplateDir = dirname(__FILE__) . '/templates/';
		$aFiles = glob( $sTemplateDir . $sWidgetName . '_*.php');
		$sReturn = '';
		$bFirst = true;
		foreach($aFiles as $sFileName){
			//читаем название файла
			$sContent = file_get_contents($sFileName);
			$aMaches = array();
			preg_match('/NAME: (.*)/', $sContent,$aMaches);
			$sName = $aMaches[1];
			$sTemplateName = str_replace( array($sTemplateDir,'.php'),'',$sFileName);
			$sSelected = $bFirst ? 'selected' : '';
			$sReturn .= '<option value="' . $sTemplateName . '" ' . $sSelected . '>' . $sName . '</option>';
			$bFirst = false;
		}

		return $sReturn;

	}

}

if (is_admin()){
	add_action('admin_init', create_function('', 'return new SotmarketAdminTab();'));
}


