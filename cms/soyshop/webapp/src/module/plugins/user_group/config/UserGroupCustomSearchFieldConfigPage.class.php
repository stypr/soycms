<?php

class UserGroupCustomSearchFieldConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
		SOY2::import("module.plugins.user_group.component.UserGroupCustomSearchFieldListComponent");
	}

	function doPost(){

		if(soy2_check_token()){

			if($_POST["create"]){
				$key = trim($_POST["custom_key"]);

				//DBへカラムを追加する
				if(SOY2Logic::createInstance("module.plugins.user_group.logic.DataBaseLogic")->addColumn($key, $_POST["custom_type"])){
					$config = UserGroupCustomSearchFieldUtil::getConfig();

					$config[$key] = array(
						"label" => trim($_POST["custom_label"]),
						"type" => $_POST["custom_type"]
					);

					UserGroupCustomSearchFieldUtil::saveConfig($config);
					$this->configObj->redirect("updated");
				}
			}
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$key = $_POST["update_advance"];
			$config = UserGroupCustomSearchFieldUtil::getConfig();
			$config[$key]["option"] = $_POST["config"]["option"];

			UserGroupCustomSearchFieldUtil::saveConfig($config);
			$this->configObj->redirect("updated");
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$key = $_POST["delete_submit"];

			//カラムの削除を試みる:SQLiteではカラムを削除できない
			SOY2Logic::createInstance("module.plugins.user_group.logic.DataBaseLogic")->deleteColumn($key);

			$config = UserGroupCustomSearchFieldUtil::getConfig();
			unset($config[$key]);

			UserGroupCustomSearchFieldUtil::saveConfig($config);
			$this->configObj->redirect("deleted");
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$configs = UserGroupCustomSearchFieldUtil::getConfig();

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId, $keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $configs[$value];
					$tmpArray[$value] = $field;
				}

				UserGroupCustomSearchFieldUtil::saveConfig($tmpArray);
				$this->configObj->redirect();
			}
		}

		$this->configObj->redirect("error");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		DisplayPlugin::toggle("error", isset($_GET["error"]));
		DisplayPlugin::toggle("deleted", isset($_GET["deleted"]));

		$this->createAdd("field_list", "UserGroupCustomSearchFieldListComponent", array(
			"list" => UserGroupCustomSearchFieldUtil::getConfig()
		));

		self::buildCreateForm();

		$this->addLabel("example_tag_list", array(
			"text" => self::buildExampleTags()
		));
	}

	private function buildCreateForm(){
		$this->addForm("create_form");

		$this->addSelect("custom_type_select", array(
			"name" => "custom_type",
			"options" => UserGroupCustomSearchFieldUtil::getTypeList()
		));
	}

	private function buildExampleTags(){
		$html = array();

		foreach(UserGroupCustomSearchFieldUtil::getConfig() as $key => $field){
			$html[] = "\t" . $field["label"] . ":\n";

			switch($field["type"]){
				case UserGroupCustomSearchFieldUtil::TYPE_INTEGER:
					$html[] = "\t<input type=\"number\" csf:id=\"custom_search_" . $key . "\">\n\n";
					break;
				case UserGroupCustomSearchFieldUtil::TYPE_RANGE:
					$html[] = "\t<input type=\"number\" csf:id=\"custom_search_" . $key . "_start\">～";
					$html[] = "<input type=\"number\" csf:id=\"custom_search_" . $key . "_end\">\n\n";
					break;
				case UserGroupCustomSearchFieldUtil::TYPE_CHECKBOX:
					if(isset($field["option"])) foreach(explode("\n", $field["option"]) as $i => $o){
						$o = trim($o);
						$html[] = "\t<input type=\"checkbox\" csf:id=\"custom_search_" . $key . "_" . $i . "\">\n";
					}
					$html[] = "\n";
					break;
				case UserGroupCustomSearchFieldUtil::TYPE_RADIO:
					if(isset($field["option"])) foreach(explode("\n", $field["option"]) as $i => $o){
						$o = trim($o);
						$html[] = "\t<input type=\"radio\" csf:id=\"custom_search_" . $key . "_" . $i . "\">\n";
					}
					$html[] = "\n";
					break;
				case UserGroupCustomSearchFieldUtil::TYPE_SELECT:
					$html[] = "\t<select csf:id=\"custom_search_" . $key . "\"><option value=\"\"></option></select>\n\n";
					break;
				default:
					$html[] = "\t<input type=\"text\" csf:id=\"custom_search_" . $key . "\">\n\n";
			}

			if($field["type"] == UserGroupCustomSearchFieldUtil::TYPE_CHECKBOX){
				$html[] = "\t" . $field["label"] . "(セレクトボックス):\n";
				$html[] = "\t<select csf:id=\"custom_search_" . $key . "_select\"><option value=\"\"></option></select>\n\n";
			}
		}

		return "\t" . trim(implode("", $html));
	}


	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
