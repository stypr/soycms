<?php

class MPFTextConfigPage extends WebPage {

	private $pluginObj;
	private $hash;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$cnf = MultiplePageFormUtil::readJson($this->hash);

			$cnf["next"] = (isset($_POST["Config"]["next"])) ? $_POST["Config"]["next"] : "";
			$cnf["description"] = (isset($_POST["Config"]["description"])) ? $_POST["Config"]["description"] : "";
			$cnf["button"] = (isset($_POST["Config"]["button"])) ? $_POST["Config"]["button"] : "";
			$cnf["template"] = (isset($_POST["Config"]["template"])) ? $_POST["Config"]["template"] : "default";

			MultiplePageFormUtil::savePageConfig($this->hash, $cnf);

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("page_name", array(
			"text" => MultiplePageFormUtil::getPageName($this->hash)
		));

		self::_buildConfigForm();
	}

	private function _buildConfigForm(){
		$cnf = MultiplePageFormUtil::readJson($this->hash);

		$this->addForm("form");

		$this->addLabel("page_type", array(
			"text" => MultiplePageFormUtil::getTypeText($cnf["type"])
		));

		$this->addTextArea("page_description", array(
			"name" => "Config[description]",
			"value" => (isset($cnf["description"])) ? $cnf["description"] : ""
		));

		$this->addSelect("next_page_type", array(
			"name" => "Config[next]",
			"options" => MultiplePageFormUtil::getPageItemList($this->hash),
			"selected" => (isset($cnf["next"])) ? $cnf["next"] : ""
		));

		$this->addInput("button_label", array(
			"name" => "Config[button]",
			"value" => (isset($cnf["button"])) ? $cnf["button"] : "",
			"attr:placeholder" => "次へ"
		));

		$this->addSelect("page_template", array(
			"name" => "Config[template]",
			"options" => MultiplePageFormUtil::getTemplateList($cnf["type"]),
			"selected" => (isset($cnf["template"])) ? $cnf["template"] : null
		));

		$this->addLabel("template_dir", array(
			"text" => dirname(MultiplePageFormUtil::getTemplateFilePath($cnf)) . "/"
		));

		$this->addLabel("default_template_file_path", array(
			"text" => MultiplePageFormUtil::getDefaultTemplateFilePath($cnf["type"])
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setHash($hash){
		$this->hash = $hash;
	}
}
