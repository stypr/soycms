<?php
SOY2::import("message.MessageManager");
function soyshop_output_user($htmlObj, SOYShop_User $user, $obj=null){

	//メールアドレス
	$htmlObj->addLabel("mail_address", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => (strpos($user->getMailAddress(), DUMMY_MAIL_ADDRESS_DOMAIN) === false) ? $user->getMailAddress() : ""
	));

	$htmlObj->addLabel("name", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $user->getName()
	));

	//フリガナ
	$htmlObj->addLabel("reading", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $user->getReading(),
	));

	//ニックネーム
	$htmlObj->addLabel("nickname", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $user->getNickname(),
	));

	$htmlObj->addLabel("gender", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => ($user->getGender() == SOYShop_User::USER_SEX_MALE) ? MessageManager::get("SEX_MALE") :
				( ($user->getGender() == SOYShop_User::USER_SEX_FEMALE) ? MessageManager::get("SEX_FEMALE") : "" )
	));

	//生年月日 年
	$htmlObj->addLabel("birth_year", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getBirthdayYear(),
	));

	//生年月日 月
	$htmlObj->addLabel("birth_month", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getBirthdayMonth(),
	));

	//生年月日 日
	$htmlObj->addLabel("birth_day", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getBirthdayDay(),
	));

	$htmlObj->addLabel("birthday_text", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $user->getBirthdayText()
	));

	//郵便番号
	$htmlObj->addLabel("zip_code", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getZipCode()
	));

	//都道府県
	$htmlObj->addLabel("area", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => SOYShop_Area::getAreaText($user->getArea())
	));

	//住所入力1
	$htmlObj->addLabel("address1", array(
		"name" => "Customer[address1]",
		"text" =>  $user->getAddress1(),
	));

	//住所入力2
	$htmlObj->addLabel("address2", array(
		"name" => "Customer[address2]",
		"text" =>  $user->getAddress2(),
	));

	//電話番号
	$htmlObj->addLabel("telephone_number", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getTelephoneNumber(),
	));

	//FAX番号
	$htmlObj->addLabel("fax_number", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getFaxNumber(),
	));

	//携帯電話番号
	$htmlObj->addLabel("cellphone_number", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getCellphoneNumber(),
	));

	//URL
	$htmlObj->addLabel("url", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getUrl(),
	));

	//勤務先名称・職種
	$htmlObj->addLabel("job_name", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" =>  $user->getJobName(),
	));

	SOYShopPlugin::load("soyshop.user.customfield");
	$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
		"mode" => "build_named_form",
		"app" => MyPageLogic::getMyPage(),
		"pageObj" => $htmlObj,
		"userId" => $user->getId()
	));
}
