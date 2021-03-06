<?php

class AddItemOrderStatusConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.add_itemorder_status.util.AddItemOrderStatusUtil");
		SOY2::imports("module.plugins.add_itemorder_status.component.*");
	}

	function doPost(){

		if(soy2_check_token()){
			$config = array();

			if(isset($_POST["number"]) && count($_POST["number"])){
				for($i = 0; $i < count($_POST["number"]); $i++){
					if(isset($_POST["number"][$i]) && (int)$_POST["number"][$i] > 0){
						if(isset($_POST["label"][$i]) && strlen($_POST["label"][$i])){
							$config[(int)$_POST["number"][$i]] = trim($_POST["label"][$i]);
						}
					}
				}
			}

			//新たに追加する項目
			if((int)$_POST["new_number"] > 0 && strlen($_POST["new_label"])){
				$config[(int)$_POST["new_number"]] = $_POST["new_label"];
			}

			AddItemOrderStatusUtil::saveConfig($config);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("status_list", "AddItemOrderStatusListComponent", array(
			"list" => AddItemOrderStatusUtil::getConfig()
		));


		//現在の注文状態の設定状況
		SOY2::import("domain.order.SOYShop_ItemOrder");
		$statusList = SOYShop_ItemOrder::getStatusList();

		$html = array();
		if(count($statusList)){
			$html[] = "<table class=\"table table-striped\" style=\"width:50%;\">";
			$html[] = "<caption>状態の設定状況</caption>";
			$html[] = "<tr><th class=\"col-lg-2\">状態ID</th><th>ラベル</th></tr>";
			foreach($statusList as $key => $label){
				$html[] = "<tr><td>" . $key . "</td><td>" . $label . "</td></tr>";
			}
			$html[] = "</table>";
			$html[] = "<br style=\"clear:left;\">";
		}

		$this->addLabel("config_detail", array(
			"html" => implode("\n", $html)
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
