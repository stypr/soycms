<?php
class FixedPointBaseInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
        //初期化時のみテーブルを作成する
        $sqls = self::getSQL();
        $dao = new SOY2DAO();

        if(preg_match_all('/CREATE.*?;/mis', $sqls, $tmp)){
            if(count($tmp[0])){
                foreach($tmp[0] as $sql){
                    try{
                        $dao->executeQuery(trim($sql));
                    }catch(Exception $e){
                        //データベースが存在する場合はスルー
                    }
                }
            }

        }
    }

	function onUnInstall(){
		//アンインストールしてもテーブルは残す
	}

	/**
	 * @return String sql for init
	 */
	private function getSQL(){
		return file_get_contents(SOY2::rootDir() . "module/plugins/common_point_base/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "fixed_point_grant", "FixedPointBaseInstall");