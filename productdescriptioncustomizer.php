<?php
if (!defined('_PS_VERSION_')){
  exit;
  }

require_once(_PS_MODULE_DIR_.'productdescriptioncustomizer/classes/DBQueryHelper.php');

class ProductDescriptionCustomizer extends Module
{
	//protected $max_image_size = 1048576;

	/**
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	public function __construct()
	{
		$this->name = 'productdescriptioncustomizer';
		$this->tab = 'font_office_features';
		$this->version = '1.0';
		$this->author = 'Linus Lundevall @prettypegs.com';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');
		$this->bootstrap = true;

		$this->module_path = _PS_MODULE_DIR_.$this->name.'/';
		$this->uploads_path = _PS_MODULE_DIR_.$this->name.'/img/';
		$this->admin_tpl_path = _PS_MODULE_DIR_.$this->name.'/views/templates/admin/';
		$this->hooks_tpl_path = _PS_MODULE_DIR_.$this->name.'/views/templates/hooks/';

		parent::__construct();

		$this->displayName = $this->l('product description customizer');
		$this->description = $this->l('Enables merchant to set different product description depending attributes selected on product page.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall? We are not friends anymore...');

		// if (!Configuration::get('IMAGECLOUDGALLERY_NAME'))
		// {
		// 	$this->warning = $this->l('No name provided');
		// }

	}

	/**
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	public function install()
	{
		if (Shop::isFeatureActive()){
    	Shop::setContext(Shop::CONTEXT_ALL);
    }

  	return parent::install() &&
  	$this->installDB() &&
    $this->registerHook('displayBackOfficeHeader') &&
    $this->registerHook('productTabContent');

    // $this->registerHook('displayFooterProduct') &&
   	//displayRightColumnProduct
	}

	/**
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		return true;
	}

	/**
	* This controls the configuration page for this module.
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	public function getContent()
	{
		$output = null;


		if (Tools::isSubmit('submit'.$this->name))
		{
			$image_cloud_gallery = strval(Tools::getValue('PDC_NAME'));
			if (!$image_cloud_gallery
				|| empty($image_cloud_gallery)
				|| !Validate::isGenericName($image_cloud_gallery)) {
				$output .= $this->displayError($this->l('Invalid Configuration value'));
			}
			else
			{
				Configuration::updateValue('PDC_NAME', $image_cloud_gallery);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}

		if (Tools::isSubmit('newItem'))
		{
			$this->addItem();
		}
		elseif (Tools::isSubmit('updateItem'))
		{
			$this->updateItem();
		}
		elseif (Tools::isSubmit('removeItem'))
		{
			$this->removeItem();
		}

		$output .= $this->renderThemeConfiguratorForm();
		return $output.$this->displayForm();
	}

	/**
	* This displays the form in the backoffice configuration page for this module.
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	public function displayForm()
	{
    // Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$helper = new HelperForm();

    // Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

    // Title and toolbar
		$helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
    	'save' =>
    	array(
    		'desc' => $this->l('Save'),
    		'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
    		'&token='.Tools::getAdminTokenLite('AdminModules'),
    		),
    	'back' => array(
    		'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
    		'desc' => $this->l('Back to list')
    		)
    	);

    // Load current value
    //$helper->fields_value['IMAGECLOUDGALLERY_NAME'] = Configuration::get('IMAGECLOUDGALLERY_NAME');

    //return $helper->generateForm($fields_form);
  }

  public function hookDisplayBackOfficeHeader()
	{
		if (Tools::getValue('configure') != $this->name)
			return;

		$this->context->controller->addCSS($this->_path.'css/admin.css');
		$this->context->controller->addJquery();
		$this->context->controller->addJS($this->_path.'js/admin.js');
	}

	/**
	* This displays on the product page.
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	public function hookProductTabContent()
	{
		$current_lang_id = Tools::getvalue('id_lang');
		$current_id_product = Tools::getvalue('id_product');

		$pdc_items = DBQueryHelper::getItemsByIdProduct($current_id_product);
		$pdc_lang_items = DBQueryHelper::getItemLangsByPDCObjects($pdc_items);

		$this->context->smarty->assign(array('current_lang_id' => $current_lang_id ));
		$this->context->smarty->assign(array('current_id_product' => $current_id_product));
		$this->context->smarty->assign(array('pdc_lang_items' => $pdc_lang_items));
		$this->context->smarty->assign(array('pdc_items' => $pdc_items));

		$this->context->controller->addJS($this->_path.'js/producttabcontent.js');
		//$this->context->controller->addCSS($this->_path.'css/productdescriptioncustomizer.css', 'all');

		return $this->display(__FILE__, 'views/templates/hook/producttabcontent.tpl');
	}

	/**
	* Creates the tables in database for this module.
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	private function installDB()
	{
		/*
			Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pdc_lang`')  &&
			Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pdc`') &&
		*/

		return (
			Db::getInstance()->Execute("
				CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pdc` (
				  `id_pdc` int(255) unsigned NOT NULL AUTO_INCREMENT,
				  `id_product` int(11) NOT NULL,
				  `id_attribute` int(11) NOT NULL,
					`enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
					`description` text,
				  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Date when this that was created.',
				  PRIMARY KEY (`id_pdc`),
				   KEY `select` (`id_pdc`,`id_product`,`id_attribute`)
				) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;") &&

			Db::getInstance()->Execute("
				CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pdc_lang` (
				  `id_pdc_lang` int(255) unsigned NOT NULL AUTO_INCREMENT,
				  `id_pdc` int(11) NOT NULL,
				  `id_lang` int(11) NOT NULL,
	  			`html` text,
				  PRIMARY KEY (`id_pdc_lang`)
				) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;")

			);

	}

	/**
	* This is suppose to set up the admin page
	* @author Linus Lundevall <developer@prettypegs.com>
	*/

	protected function renderThemeConfiguratorForm()
	{
		$id_shop = (int)$this->context->shop->id;
	//		$items = array();

		$this->context->smarty->assign('htmlcontent', array(
			'admin_tpl_path' => $this->admin_tpl_path,
			'hooks_tpl_path' => $this->hooks_tpl_path,

			'info' => array(
				'module' => $this->name,
				'name' => $this->displayName,
				'version' => $this->version,
				'psVersion' => _PS_VERSION_,
				'context' => (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 0) ? 1 : ($this->context->shop->getTotalShops() != 1) ? $this->context->shop->getContext() : 1
				)
			));

		$attributes = DBQueryHelper::getAllAttributes();
		$products = DBQueryHelper::getAllProducts();
		$items = DBQueryHelper::getAllItems();
		$languages = Language::getLanguages();

		//$this->context->smarty->assign(array('items' => $items));
  	$this->context->smarty->assign(array('attributes' => $attributes));
		$this->context->smarty->assign(array('products' => $products));
		$this->context->smarty->assign(array('languages' => $languages));

		// This creates an array with a pdc row and its associate pdc_lang rows in one array.
		// Like this: itemsAndLang[ [item => [], languages => [] ] ]
		$itemsAndLang = array();
		foreach($items as $item){

			$tempArray = array();
			$tempArray['item'] = $item;
			$tempArray['languages'] = DBQueryHelper::getPDCLang($item['id_pdc']);

			array_push($itemsAndLang, $tempArray);
		}

		$this->context->smarty->assign('htmlItems', array('items' => $itemsAndLang,
			'postAction' =>
			'index.php?tab=AdminModules&configure='.$this->name
			.'&token='.Tools::getAdminTokenLite('AdminModules')
			.'&tab_module=other&module_name='.$this->name.'',
			'id_shop' => $id_shop
			));

		return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
	}


	/**
	* Saves an item in admin panel
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	protected function updateItem()
	{
		$id_item = Tools::getValue('item_id');
		$id_product = Tools::getValue('id_product');
		$id_attribute = Tools::getValue('id_attribute');
		$description = Tools::getValue('description');
		$result = DBQueryHelper::updateItem($id_item, $id_attribute, $id_product, $description);
		$languages = Language::getLanguages();

		foreach($languages as $lang){
			$html = Tools::getValue('item_lang_' . $lang['id_lang']);
			DBQueryHelper::updateItemLang($id_item, $lang['id_lang'], $html);
		}

		if(!$result){
			$this->context->smarty->assign('error', $this->l('An error occurred while saving data.'));
			return false;
		}
		else{
			$this->context->smarty->assign('confirmation', $this->l('Successfully updated.'));
			return true;
		}
	}

	/**
	* Used for adding new images to the cloud gallery.
	* @author Linus Lundevall <developer@prettypegs.com>
	*/
	protected function addItem()
	{
		$attributes = DBQueryHelper::getAllAttributes();

		$id_product = Tools::getValue('id_product');
		$id_attribute = Tools::getValue('id_attribute');
		$description = Tools::getValue('description');

		$insertResultId = DBQueryHelper::insertItem($id_attribute, $id_product, $description);
		$languages = Language::getLanguages();

		foreach($languages as $lang){
			$html = Tools::getValue('item_lang_' . $lang['id_lang']);
			DBQueryHelper::insertItemLang($insertResultId, $lang['id_lang'], $html);
		}

		if (!$insertResultId){
			$this->context->smarty->assign('error', $this->l('An error occurred while saving data.'));
			return false;
		}
		else{
			$this->context->smarty->assign('confirmation', $this->l('New preference successfully added.'));
			return true;
		}
	}


	protected function removeItem()
	{
		$id_item = (int)Tools::getValue('item_id');

		Db::getInstance()->delete(_DB_PREFIX_.'pdc', 'id_pdc = '.$id_item);
		Db::getInstance()->delete(_DB_PREFIX_.'pdc_lang', 'id_pdc_lang = '.$id_item);

		if (Db::getInstance()->Affected_Rows() > 0)
		{
			Tools::redirectAdmin('index.php?tab=AdminModules&configure='.$this->name.'&conf=6&token='.Tools::getAdminTokenLite('AdminModules'));
		}
		else{
			$this->context->smarty->assign('error', $this->l('Can\'t delete the preference.'));
		}
	}

}

