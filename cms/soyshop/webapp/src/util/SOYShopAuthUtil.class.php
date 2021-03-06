<?php

class SOYShopAuthUtil {

	function __construct(){
		SOY2::import("domain.config.SOYShop_ShopConfig");
	}

	private function _authes(){
		return array(
			"HOME" => true,
			"EXTENSION" => true,
			"ORDER" => true,
			"ITEM" => true,
			"USER" => true,
			"REVIEW" => true,
			"CONFIG" => true,
			"PLUGIN" => true,
			"SITE" => true,		//ページの作成やテンプレートの編集等
			"OPERATE" => true,	//更新の操作に関するもの,
			"SOYAPP" => true	//SOY InquiryやSOY Mail
		);
	}

	public static function setAuthConstant(){
		$authes = self::_authes();

		//初期管理者の時は全操作を許可する
		$session = SOY2ActionSession::getUserSession();
		if($session->getAttribute("isdefault")){
			//何もしない

		//App権限を取得する
		}else{
			$shopAuthLevel = (int)$session->getAttribute("app_shop_auth_level");
			switch($shopAuthLevel){
				case 1:	//一般管理者
					//何もしない
					break;
				case 2:	//受注管理者 設定、プラグインとサイト管理を封じる
					$authes["CONFIG"] = false;
					$authes["PLUGIN"] = false;
					$authes["SITE"] = false;
					break;
				case 3:	//管理制限者	更新の操作を封じる
					$authes["CONFIG"] = false;
					$authes["PLUGIN"] = false;
					$authes["SITE"] = false;
					$authes["OPERATE"] = false;
					$authes["SOYAPP"] = false;
					break;
				case 10:	//商品管理制限者
					foreach($authes as $key => $auth){
						$authes[$key] = false;
					}
					$authes["ITEM"] = true;
					$authes["OPERATE"] = true;
					break;
				case 0:	//すべてfalse
				default:
					foreach($authes as $key => $bool){
						$authes[$key] = false;
					}
			}
		}

		//管理画面で設定した内容を反映
		if($authes["ORDER"] || $authes["USER"] || $authes["ITEM"]){
			$config = SOYShop_ShopConfig::load();
			if($authes["ORDER"]) $authes["ORDER"] = $config->getDisplayOrderAdminPage();
			if($authes["USER"]) $authes["USER"] = $config->getDisplayUserAdminPage();
			if($authes["ITEM"]) $authes["ITEM"] = $config->getDisplayItemAdminPage();
		}
		if($authes["REVIEW"]) $authes["REVIEW"] = SOYShopPluginUtil::checkIsActive("item_review");

		//定数の設定
		foreach($authes as $key => $bool){
			if(!defined("AUTH_" . $key)) define("AUTH_" . $key, $bool);
		}

		//soy:display="app_limit_function"の設定
		DisplayPlugin::toggle("app_limit_function", AUTH_OPERATE);
		DisplayPlugin::toggle("app_limit_function_rv", AUTH_OPERATE);	//1ページで二回使用している場合の予備
	}

	//権限を調べ、開いてはいけないページの場合は
	public static function checkAuthEachPage($classPath){
		//トップページのみ特別な処理
		if(!AUTH_HOME && (strpos($classPath, "IndexPage") === 0 || !strlen($classPath))){
			if(AUTH_ORDER) SOY2PageController::jump("Order");
			if(AUTH_ITEM) SOY2PageController::jump("Item");
			if(AUTH_USER) SOY2PageController::jump("User");

			// @ToDo 該当しないページがあった場合はどうしよう？
		}

		if(!self::_check($classPath)) SOY2PageController::jump("");
	}

	private static function _check($classPath){
		if(strpos($classPath, "Order") !== false){
			//注文の時のみ振る舞いが異なる
			if(($classPath == "Order.IndexPage" || $classPath == "OrderPage") && !AUTH_ORDER) return false;

			//商品管理のみアカウントの場合は注文すべてのページを見れなくする
			if(AUTH_ITEM && !AUTH_EXTENSION && !AUTH_ORDER) return false;
		}else{
			if(!AUTH_EXTENSION && strpos($classPath,"Extension") === 0) return false;
			//if(!AUTH_ORDER && strpos($classPath,"Order") === 0) return false;
			if(!AUTH_ITEM && strpos($classPath,"Item") === 0) return false;
			if(!AUTH_USER && strpos($classPath,"User") === 0) return false;
			if(!AUTH_REVIEW && strpos($classPath,"Review") === 0) return false;
			if(!AUTH_PLUGIN && strpos($classPath,"Plugin") === 0) return false;
			if(!AUTH_CONFIG && strpos($classPath,"Config") === 0) return false;
			if(!AUTH_SITE && strpos($classPath,"Site") === 0) return false;
		}

		//何もなければtrue
		return true;
	}
}
