<?php
/**
 * @class Site.Template.EditorPage
 * @date 2009-11-27T03:36:27+09:00
 * @author SOY2HTMLFactory
 */
class EditorPage extends WebPage{

	private $moduleId;
	private $modulePath;
	private $iniPath;

	function doPost(){

		if(soy2_check_token()){
			$config = $_POST["config"];
			
			//make ini
			$array = array();
			$array[] = "name=" . $config["name"];
			$array[] = "content=" . rawurlencode($config["content"]);
			
			file_put_contents($this->iniPath, implode("\n", $array));
			
			$funcName = str_replace(".", "_", substr($this->moduleId, strpos($this->moduleId, ".")));
			
			//make php
			$array = array();
			$array[] = "<?php /* this script is generated by soyshop. */";
			$array[] = "function soyshop_" . $funcName . '($html,$htmlObj){';
			$array[] = "";
			$array[] = "ob_start();";
			$array[] = "";
			$array[] = "echo <<<HTML";
			$array[] = trim($config["content"]);
			$array[] = "HTML;";
			$array[] = "";
			$array[] = "ob_end_flush();"; 
			$array[] = "";
			$array[] = "}";
			$array[] = "?>";
			file_put_contents($this->modulePath,implode("\n", $array));
		}

		SOY2PageController::jump("Site.Template.Module.html.EditorPage?updated&moduleId=" . $this->moduleId);
	}

	function __construct($args){
		
		$this->moduleId = (isset($_GET["moduleId"])) ? htmlspecialchars(str_replace("/", ".", $_GET["moduleId"])) : null;

		$moduleDir = SOYSHOP_SITE_DIRECTORY . ".module/html/";
		
		$this->modulePath = $moduleDir . str_replace(".", "/", $this->moduleId) . ".php";
		$this->iniPath =$moduleDir . str_replace(".", "/", $this->moduleId) . ".ini";

		parent::__construct();
		
		$ini = @parse_ini_file($this->iniPath);

		$this->addForm("update_form");
		
		$this->addLabel("module_name_text", array(
			"text" => (isset($ini["name"])) ? $ini["name"] : $this->moduleId
		));
		
		$this->addInput("module_name", array(
			"name" => "config[name]",
			"value" => (isset($ini["name"])) ? $ini["name"] : $this->moduleId
		));

		$this->addLabel("module_id", array(
			"text" => $this->moduleId
		));

		$content = (isset($ini["content"])) ? $ini["content"] : ""; 
		$this->addTextArea("module_content", array(
			"name" => "config[content]",
			"value" => $this->getModuleContent($content, file_get_contents($this->modulePath))
		));
		
		$this->addLabel("module_example", array(
			"text" => "<!-- shop:module=\"html." . $this->moduleId."\" -->\n" . @$ini["name"] . "のモジュールを読み込みます。\n<!-- /shop:module=\"html." . $this->moduleId."\" -->"
		));
	}
	
	function getModuleContent($ini, $str){
		if(strlen($ini) > 0){
			preg_match('/<<<HTML(.*)HTML;/s', $str, $match);
			return (isset($match[1])) ? trim($match[1]) : "";
		}
		
		$array = array();				
		return implode("\n", $array);
	}
}
?>