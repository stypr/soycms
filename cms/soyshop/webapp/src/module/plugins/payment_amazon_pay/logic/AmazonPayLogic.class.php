<?php
require dirname(dirname(__FILE__)) . '/lib/AmazonPay/Client.php';

Use AmazonPay\Client;

class AmazonPayLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function pay(SOYShop_Order $order){
		$referenceId = $_REQUEST['orderReferenceId'];

		$client = self::_client();

		// (2) 注文情報をセット
		$client->SetOrderReferenceDetails(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_order_reference_id' => $referenceId,
			'amount' => $order->getPrice(),
			'currency_code' => 'JPY',
			'seller_note' => 'ご購入ありがとうございます',
			'seller_order_id' => $order->getTrackingNumber(),
			'store_name' => 'ショップ名',
		));

		if(!$client->success) return array(null, null);

		// (3) 注文情報を確定
		$client->confirmOrderReference(array(
			'amazon_order_reference_id' => $referenceId
		));

		if(!$client->success) return array(null, null);

		// (4) オーソリをリクエスト
		$response = $client->authorize(array(
			'amazon_order_reference_id' => $referenceId,
			'authorization_amount' => $order->getPrice(),
			'authorization_reference_id' => $order->getTrackingNumber() . "_" . time(),
			'seller_authorization_note' => 'Authorizing payment',
			'transaction_timeout' => 0,
		));

		$result = $response->response;
		if($result["Status"] != 200) return array(null, null);

		preg_match('/<AmazonAuthorizationId>(.*)<\/AmazonAuthorizationId>/', $result["ResponseBody"], $tmp);
		if(!isset($tmp[0])) return array(null, null);

		// オーソリが成功したか確認
		$amazonAuthorizationId = $tmp[1];

		// (5) 注文を確定
		$response = $client->capture(array(
			'amazon_authorization_id' => $amazonAuthorizationId,
			'capture_amount' => $order->getPrice(),
			'currency_code' => 'JPY',
			'capture_reference_id' => $order->getTrackingNumber() . "_" . time(),
			'seller_capture_note' => '購入が完了しました',
		));

		$result = $response->response;

		// 注文の確定に失敗したらオーソリを取り消して、注文をクローズする
		if($result['Status'] != 200) {
			self::_cancel($client, $referenceId, $amazonAuthorizationId);
			return array(null, null);
		}

		return array($referenceId, $amazonAuthorizationId);
	}

	function cancel($referenceId, $amazonAuthorizationId){
		self::_cancel(self::_client(), $referenceId, $amazonAuthorizationId);
	}

	private function _client(){
		$cnf = AmazonPayUtil::getConfig();
		$sandbox = (isset($cnf["sandbox"]) && (int)$cnf["sandbox"]);

		$cnf = AmazonPayUtil::getConfig(false);

		//様々なところで使い回す
		if(!defined("AMAZON_PAY_MERCHANT_ID")) define("AMAZON_PAY_MERCHANT_ID", $cnf["merchant_id"]);

		// (1) Clientインスタンスを作成
		return new Client(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'access_key' => $cnf["access_key_id"],
			'secret_key' => $cnf["secret_access_key"],
			'client_id' => $cnf["client_id"],
			'currency_code' => 'jpy',
			'region' => 'jp',
			'sandbox' => $sandbox,
		));
	}

	private function _cancel(Client $client, $referenceId, $amazonAuthorizationId){
		$client->cancelOrderReference(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_order_reference_id' => $referenceId,
		));
		$client->closeAuthorization(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_authorization_id' => $amazonAuthorizationId,
		));
	}
}
