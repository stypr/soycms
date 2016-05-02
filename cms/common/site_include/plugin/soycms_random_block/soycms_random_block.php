<?php
SOYCMS_Random_Block_Plugin::registerPlugin();

class SOYCMS_Random_Block_Plugin{
	
	const PLUGIN_ID = "soycms_random_block";
	
	
	function getId(){
		return SOYCMS_Random_Block_Plugin::PLUGIN_ID;
	}
	
	/**
	 * 初期化
	 */
	function init(){
		
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY CMS記事ランダム表示ブロックプラグイン",
			"description"=>"プラグインブロックで記事をランダムに表示します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

        if(CMSPlugin::activeCheck($this->getId())){
            CMSPlugin::addPluginConfigPage($this->getId(),array(
                $this,"config_page"
            ));

            CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
            CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
        }
	}

    function onLoad(){
        $pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

        //ブログページか調べる
        $template = "";
        try{
            $blog = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId);
            $uri = str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $_SERVER["PATH_INFO"]);

            //トップページ
            if($uri === (string)$blog->getTopPageUri()){
                $template = $blog->getTopTemplate();
                //アーカイブページ		
            }else if(strpos($uri, $blog->getCategoryPageUri()) === 0 || strpos($uri, $blog->getMonthPageUri()) === 0){
                $template = $blog->getArchiveTemplate();
                //記事ごとページ
            }else{
                $template = $blog->getEntryTemplate();
            }
        }catch(Exception $e){
            try{
                $template = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getTemplate();
            }catch(Exception $e){
                return array();
            }
        }

        try{
            $blocks = SOY2DAOFactory::create("cms.BlockDAO")->getByPageId($pageId);
        }catch(Exception $e){
            return array();
        }

        if(!count($blocks)) return array();

        $block = null;
        foreach($blocks as $obj){
            if($obj->getClass() == "PluginBlockComponent"){
                $block = $obj;
            }
        }

        if(is_null($block)) return array();

        //ラベルIDを取得とデータベースから記事の取得件数指定
        $labelId = null;
        $count = null;
        if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
            if(preg_match('/cms:label=\"(.*?)\"/', $tmp[1], $ltmp)){
                if(isset($ltmp[1]) && is_numeric($ltmp[1])) $labelId = (int)$ltmp[1];
            }
            if(preg_match('/cms:count=\"(.*?)\"/', $tmp[1], $ctmp)){
                if(isset($ctmp[1]) && is_numeric($ctmp[1])) $count = (int)$ctmp[1];
            }
        }else{
            return array();
        }

        $entryDao = SOY2DAOFactory::create("cms.EntryDAO");
        $sql = "SELECT ent.* FROM Entry ent ".
             "JOIN EntryLabel lab ".
             "ON ent.id = lab.entry_id ".
             "WHERE ent.openPeriodStart < " . time() . " ".
             "AND ent.openPeriodEnd >= " .time() . " ".
             "AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ";
        $binds = array();
	
        //ラベルIDを指定する場合
        if(isset($labelId)){
            $sql .= "AND lab.label_id = :labelId ";
            $binds[":labelId"] = $labelId;
        }

        $sql .= "GROUP BY ent.id ";

        if(SOY2DAOConfig::type() == "mysql"){
            $sql .= "ORDER BY Rand() ";
        }else{
            $sql .= "ORDER BY Random() ";
        }

        if(isset($count) && $count > 0) {
            $sql .= "Limit " . $count;
        }
		
        try{
            $res = $entryDao->executeQuery($sql, $binds);
        }catch(Exception $e){
            $res = array();
        }

        if(!count($res)) return array();

        $entries = array();
        foreach($res as $v){
            $entries[] = $entryDao->getObject($v);
        }

        return $entries;
    }

    function returnPluginId(){
        return self::PLUGIN_ID;
    }
	
	
	/**
	 * 設定画面の表示
	 */
	function config_page($message){
        SOY2::import("site_include.plugin.soycms_random_block.config.RandomBlockConfigPage");
        $form = SOY2HTMLFactory::createInstance("RandomBlockConfigPage");
        $form->setPluginObj($this);
        $form->execute();
        return $form->getObject();
	}
	
	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
				
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_Random_Block_Plugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_Random_Block_Plugin();
		}
		CMSPlugin::addPlugin(SOYCMS_Random_Block_Plugin::PLUGIN_ID,array($obj,"init"));
	}
}
?>