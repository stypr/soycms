<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class DeliverySameDayShippingBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		$obj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(get_class($obj) != "SOYShop_Page"){
			return;
		}
		
		SOY2::import("module.plugins.delivery_same_day_shipping.util.DeliverySameDayShippingUtil");
		$values = SOY2Logic::createInstance("module.plugins.delivery_same_day_shipping.logic.ShippingDateLogic", array("config" => DeliverySameDayShippingUtil::getConfig()))->get();
		
		//発送予定日の配列
		$bArray = explode("-", date("Y-n-j", $values[0]));
		
		//到着予定日の配列
		$aArray = explode("-", date("Y-n-j", $values[1]));
		
		$page->addLabel("shipping_year", array(
			"soy2prefix" => "dsd",
			"text" => $bArray[0]
		));
		
		$page->addLabel("shipping_month", array(
			"soy2prefix" => "dsd",
			"text" => $bArray[1]
		));
		
		$page->addLabel("shipping_day", array(
			"soy2prefix" => "dsd",
			"text" => $bArray[2]
		));
		
		$page->addLabel("arrival_year", array(
			"soy2prefix" => "dsd",
			"text" => $aArray[0]
		));
		
		$page->addLabel("arrival_month", array(
			"soy2prefix" => "dsd",
			"text" => $aArray[1]
		));
		
		$page->addLabel("arrival_day", array(
			"soy2prefix" => "dsd",
			"text" => $aArray[2]
		));
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "delivery_same_day_shipping", "DeliverySameDayShippingBeforeOutput");