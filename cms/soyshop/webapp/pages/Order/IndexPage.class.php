<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
SOY2::import("domain.config.SOYShop_ShopConfig");
class IndexPage extends WebPage{

	function doPost(){

		if(!soy2_check_token()) SOY2PageController::jump("Order");

		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

		if(isset($_POST["do_change_order_status"])){
			$logic->changeOrderStatus($_POST["orders"], $_POST["do_change_order_status"]);
		}

		if(isset($_POST["do_change_payment_status"])){
			$logic->changePaymentStatus($_POST["orders"], $_POST["do_change_payment_status"]);
		}

		SOY2PageController::jump("Order?updated");
		exit;
	}

	function __construct($args){
		parent::__construct();

		$config = SOYShop_ShopConfig::load();

		//検索条件のリセット
		if(isset($_GET["reset"])){
			self::setParameter("page", 1);
			self::setParameter("sort", null);
			self::setParameter("search", array());
		}

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : self::getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = self::getParameter("sort");
		self::setParameter("page", $page);

		//検索条件
		$search = self::getParameter("search");
		//$search = (isset($_GET["search"])) ? $_GET["search"] : array();
		//検索用のロジック作成
		$searchLogic = SOY2Logic::createInstance("logic.order.SearchOrderLogic");

		//フォームの作成
		$form = self::buildSearchForm($search);
		$form = (array)SOY2::cast("object",$form);//再変換をかける

		//注文一覧は標準設定で過去一年分にする
		if(empty($search) && $config->getIsOrderListOneYearsWonth()){
			$form["orderDateStart"] = date("Y-m-d", time() - 365 * 24 * 60 * 60);
		}

		//検索条件の投入と検索実行
		$searchLogic->setSearchCondition($form);
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$total = $searchLogic->getTotalCount();
		$orders = $searchLogic->getOrders();

		//表示順リンク
		self::buildSortLink($searchLogic,$sort);

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($orders);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Order");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);

		//操作周り
		$this->addForm("order_form");

		//管理画面から注文(argsがある場合は、自動でユーザ番号を入れておきたい)
		$this->addLink("order_link", array(
			"link" => SOY2PageController::createLink("Order.Register")
		));

		//項目の表示に関して
		$itmCnt = 0;
		foreach($config->getOrderItemConfig() as $key => $b){
			if($b) $itmCnt++;

			$this->addModel($key . "_show", array(
				"visible" => $b
			));

			$this->addModel($key . "_form_show", array(
				"visible" => $b
			));
		}

		foreach(range(0,1) as $i){
			$this->addModel("col_count_" . $i, array(
				"attr:colspan" => $itmCnt + 2
			));
		}


		//注文結果を出力
		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders
		));

		$orderCnt = count($orders);
		$this->addModel("order_exists", array(
			"visible" => ($orderCnt > 0)
		));

		$this->addModel("no_result", array(
			"visible" => ($orderCnt === 0 && !empty($search))
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Order") . "?reset",
			"visible" => (!empty($search))
		));

		/* 出力用 */
		$this->createAdd("module_list", "_common.Order.ExportModuleListComponent", array(
			"list" => self::getExportModuleList()
		));

		$this->addForm("export_form", array(
			"action" => SOY2PageController::createLink("Order.Export")
		));

		$this->addInput("query", array(
			"name" => "search",
			"value" => (isset($_GET["search"])) ? http_build_query($_GET["search"]) : ""
		));

		self::buildExtensionArea();
	}

	/**
	 * 検索フォームを作成する
	 */
	private function buildSearchForm($search){

		$obj = (object)$search;

		$form = $this->create("search_form", "_common.Order.SearchFormComponent");

		SOY2::cast($form, $obj);

		$this->add("search_form", $form);

		return $form;
	}

	private function buildExtensionArea(){
		SOYShopPlugin::load("soyshop.order.upload");
		$list = SOYShopPlugin::invoke("soyshop.order.upload", array(
			"mode" => "list"
		))->getList();

		DisplayPlugin::toggle("upload_list", count($list));

		$this->createAdd("upload_extension_list", "_common.Order.UploadExtensionListComponent", array(
			"list" => $list
		));
	}

	private function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			self::setParameter($key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Order.Search:" . $key);
		}
		return $value;
	}

	private function setParameter($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("Order.Search:" . $key, $value);
	}

	private function buildSortLink(SearchOrderLogic $logic, $sort){

		$link = SOY2PageController::createLink("Order");

		$sorts = $logic->getSorts();

		foreach($sorts as $key => $value){

			$text = (!strpos($key,"_desc")) ? "▲" : "▼";
			$title = (!strpos($key,"_desc")) ? "昇順" : "降順";

			$this->addLink("sort_${key}", array(
				"text" => $text,
				"link" => $link . "?sort=" . $key,
				"title" => $title,
				"class" => ($sort === $key) ? "sorter_selected" : "sorter"
			));
		}
	}

	private function getExportModuleList(){
		SOYShopPlugin::load("soyshop.order.export");
		$list = SOYShopPlugin::invoke("soyshop.order.export", array(
			"mode" => "list"
		))->getList();

		DisplayPlugin::toggle("export_module_menu", (count($list) > 0));

		return $list;
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "tools/soy2_date_picker.css"
		);
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "tools/soy2_date_picker.pack.js"
		);
	}
}
