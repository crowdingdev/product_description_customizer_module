<?php

class DBQueryHelper extends ObjectModel
{

	/**
	* All attributes with name as of language with id = 1
	* @author Linus Karlsson
	*/
	public static function getAllAttributes()
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT  a.id_attribute, al.name FROM '._DB_PREFIX_.'attribute a'.
			' JOIN '._DB_PREFIX_.'attribute_lang al ON a.id_attribute = al.id_attribute '.
			' WHERE al.id_lang = 1'
			);
		return $result;
	}

	/**
	*	All products in system
	*	@author Linus Karlsson
	*/
	public static function getAllProducts()
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT p.id_product, pl.name FROM '._DB_PREFIX_.'product p '.
			' JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product '.
			' WHERE pl.id_lang = 1'
			);
		return $result;
	}

	/**
	*	Insert a new pdc-row into database
	* @author Linus Karlsson
	*/
	public static function insertItem($id_attribute, $id_product, $description)
	{
		$sql = '
		INSERT INTO '._DB_PREFIX_.'pdc (`id_attribute`,`id_product`, `description`)
		VALUES (' .
			(int)$id_attribute.', '.
			(int)$id_product.', '.
			'\'' . pSQL($description) .'\'' .
			')';

		$result = Db::getInstance()->Execute($sql);

		if($result)
		{
			return Db::getInstance()->Insert_ID();
		}
		else
		{
			return false;
		}
	}


	/**
	*	Insert a new pdc-row into database
	* @author Linus Karlsson
	*/
	public static function insertItemLang($id_pdc, $id_lang, $html)
	{
		$sql = '
		INSERT INTO '._DB_PREFIX_.'pdc_lang (`id_pdc`,`id_lang`, `html`)
		VALUES (' .
			(int)$id_pdc.', '.
			(int)$id_lang.', '.
			'\'' . pSQL($html, true).'\'' .
			')';

		$result = Db::getInstance()->Execute($sql);

		if($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*	Update a pdc-row in database
	* @author Linus Karlsson
	*/
	public static function updateItem($id_item, $id_attribute, $id_product, $description)
	{
		$result = Db::getInstance()->execute(
			'UPDATE `'._DB_PREFIX_.'pdc` SET
				id_attribute = '. (int)$id_attribute .', '.
			' description = \''. pSQL($description, true).'\' ,' .
			' id_product = '. (int)$id_product .' '.
			' WHERE id_pdc = '. (int)$id_item
			);
		return $result;
	}


	/**
	*	Update a pdc_lang-row in database
	* @author Linus Karlsson
	*/
	public static function updateItemLang($id_pdc, $id_lang, $html)
	{

		$sqlStr = '
				UPDATE `'._DB_PREFIX_.'pdc_lang` SET '.
			'	html = \'' . pSQL($html, true) . '\' ' .
			' WHERE id_pdc = '. (int)$id_pdc .
			' AND id_lang = '. (int)$id_lang;

		$result = Db::getInstance()->execute($sqlStr);
		return $result;
	}



	/**
	*	List all pdc-rows
	* @author Linus Karlsson
	*/
	public static function getAllItems()
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT * FROM '._DB_PREFIX_.'pdc' .
			' ORDER BY id_product ASC'
			);
		return $result;
	}

	/**
	*	List all pdc-items with enabled = 1
	* @author Linus Karlsson
	*/
	public static function getAllEnabledItems()
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT * FROM '._DB_PREFIX_.'pdc'.
			' WHERE enabled = 1 '
			);
		return $result;
	}


	/**
	*	Get items items for a product 
	* @author Linus Karlsson
	*/
	public static function getItemsByIdProduct($id_product)
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT * FROM '._DB_PREFIX_.'pdc '.
			' WHERE id_product = '. (int)$id_product
			);
		return $result;
	}


	/**
	*	Get all lang items by sending pdc items as param.
	* @author Linus Karlsson
	*/
	public static function getItemLangsByPDCObjects($pdc_objects)
	{

		$pdc_ids = array();
    foreach($pdc_objects as $k=>$v) {
        $pdc_ids[]= $v['id_pdc'];
    }

		$result = Db::getInstance()->ExecuteS('
			SELECT * FROM '._DB_PREFIX_.'pdc_lang '.
			' WHERE id_pdc in ('. implode(', ', $pdc_ids) . ') '
			);
		return $result;
	}

	/**
	*	Get all lang items for a pdc item with joind ps_lang table
	* @author Linus Karlsson
	*/
	public static function getPDCLang($id_pdc)
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT * FROM '._DB_PREFIX_.'pdc_lang pdc_l'.
			' INNER JOIN '._DB_PREFIX_. 'lang l ON l.id_lang = pdc_l.id_lang'  .
			' WHERE id_pdc = '. (int)$id_pdc
			);
		return $result;
	}


}

?>