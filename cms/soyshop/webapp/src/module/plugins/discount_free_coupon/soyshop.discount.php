<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SOYShopDiscountFreeCouponModule extends SOYShopDiscount{
	private $config;
	private $dao;

	function __construct(){
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		SOY2::imports("module.plugins.discount_free_coupon.util.*");
		$this->dao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$util = new DiscountFreeCouponUtil();
		$this->config = $util->getConfig();
	}

	function clear(){
		$cart = $this->getCart();

		$cart->removeModule("discount_free_coupon");
		$cart->clearAttribute("discount_free_coupon.code");
		$cart->clearOrderAttribute("discount_free_coupon.code");
	}

	function doPost($param){
		$cart = $this->getCart();
		$code = trim($param["coupon_codes"][0]);

		if(isset($code)){
			try{
				$coupon = $this->dao->getByCouponCodeAndNoDelete($code);
			}catch(Exception $e){
				$coupon = new SOYShop_Coupon();
			}

			$couponId = $coupon->getId();

			if(isset($couponId)){
				$module = new SOYShop_ItemModule();
				$module->setId("discount_free_coupon");
				$module->setName(MessageManager::get("MODULE_NAME_COUPON"));
				$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる

				//クーポンのタイプにより、割引額を変える
				$couponType = $coupon->getCouponType();
				//値引き額
				if($couponType == SOYShop_Coupon::TYPE_PRICE){
					$discount = $coupon->getDiscount();
					//割引金額：商品合計より大きくはならない。
					$discount = min($discount, $cart->getItemPrice());

				//値引き率
				}elseif($couponType == SOYShop_Coupon::TYPE_PERCENT){
					$discount = $cart->getItemPrice() * $coupon->getDiscountPercent() / 100;

				//送料無料
				}elseif($couponType == SOYShop_Coupon::TYPE_DELIVERY){
					foreach($cart->getModules() as $moduleId => $obj){
						if(strpos($moduleId, "delivery") === 0){
							$discount = $obj->getPrice();
						}
					}
				//念のため
				}else{
					$discount = 0;
				}

				$module->setPrice(0 - $discount);//負の値

				if($discount > 0){
					$cart->addModule($module);

					//属性の登録
					$cart->setAttribute("discount_free_coupon.code", $code);
					$cart->setOrderAttribute("discount_free_coupon.code", MessageManager::get("MODULE_NAME_COUPON"), $code);

				}
			}
		}
	}

	function order(){
		//処理はorder.completeで行う
	}

	/**
	 * クーポンが使用可能か？調べる
	 */
	function hasError($param){

		$cart = $this->getCart();
		$error = "";

		//クーポンが入力されなかった場合は何もしない
		if(isset($param["coupon_codes"][0]) && strlen($param["coupon_codes"][0]) > 0){
			$code = trim($param["coupon_codes"][0]);

			//ユーザIDが取得できなかった場合、念の為、ユーザテーブルからオブジェクトを取得
			$userId = $cart->getAttribute("logined_userid");
			if(is_null($userId)) $userId = self::getUserIdByMailAddress($cart->getCustomerInformation()->getMailAddress());

			if(!isset($param["coupon_codes"]) || !is_array($param["coupon_codes"]) || count($param["coupon_codes"]) === 0){
				//$error = "クーポンコードを入力してください。";
			}elseif(!self::checkItemPrice()){
				$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
			}else{
				$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic");
				if(!$logic->checkUsable($code, (int)$userId)){
					$error = MessageManager::get("INVALID_COUPON_CODE");
				}elseif(!$logic->checkEachCouponUsable($code, (int)$cart->getItemPrice())){
					$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
				}else{
					//
				}
			}

			$cart->setAttribute("discount_free_coupon.code", $code);
		}

		if(strlen($error) > 0){
			$cart->setAttribute("discount_free_coupon.error", $error);
			return true;
		}else{
			$cart->clearAttribute("discount_free_coupon.error");
			return false;
		}
	}

	function getError(){
		return $this->getCart()->getAttribute("discount_free_coupon.error");
	}

	function getName(){
		if(self::checkItemPrice()){
			return MessageManager::get("MODULE_NAME_COUPON");
		}else{
			//使用可能金額の範囲外ならこのモジュールは表示しない
			return "";
		}
	}

	private function getUserIdByMailAddress($mailAddress){
		//userIdを取得する
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mailAddress)->getId();
		}catch(Exception $e){
			return null;
		}
	}

	/**
	 * 使用可能金額の範囲内におさまっているかどうかを返す
	 * @return Boolean
	 */
	 private function checkItemPrice(){
  		$min = (isset($this->config["min"]) && strlen($this->config["min"]) && is_numeric($this->config["min"])) ? (int)$this->config["min"] : 0;
 		$max = (isset($this->config["max"]) && strlen($this->config["max"]) && is_numeric($this->config["max"])) ? (int)$this->config["max"] : 1000000000; //$maxが空だった場合、限りなく大きな数字を代入しておく

  		$total = $this->getCart()->getItemPrice();
  		return ($total > $min || $total < $max);
  	}

	function getDescription(){
		$code  = $this->getCart()->getAttribute("discount_free_coupon.code");

		$html = array();
		$html[] = "<table><tr><th>" . MessageManager::get("INPUT_COUPON_CODE") . "</th><td>";
		$html[] = '<input type="text" size="40" name="discount_module[discount_free_coupon][coupon_codes][]" value="'.htmlspecialchars($code, ENT_QUOTES, "UTF-8").'" />';
		$html[]='<br/>';
		$html[] = '</td></table>';

		return implode("", $html);
	}
}
SOYShopPlugin::extension("soyshop.discount", "discount_free_coupon", "SOYShopDiscountFreeCouponModule");
