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
		INSERT INTO '._DB_PREFIX_.'pdc (`id_attribute`,`id_product`)
		VALUES (' .
			(int)$id_attribute.', '.
			(int)$id_product.' '.
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
	public static function updateItem($id_item, $id_attribute, $id_product)
	{
		$result = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'pdc` SET
			id_attribute = '. (int)$id_attribute .', '.
			'id_product = '. (int)$id_product .' '.
			' WHERE id_pdc = '. (int)$id_item
			);
		return $result;
	}

	/**
	*	List all pdc-rows
	* @author Linus Karlsson
	*/
	public static function getAllItems()
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT * FROM '._DB_PREFIX_.'pdc'
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

}

?>