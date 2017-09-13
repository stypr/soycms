<?php
class GroupFieldFormComponent {

	public static function buildForm($fieldId, $field, $value = null, $isMyPage = false, $hasStyle = false) {

		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
		$nameProperty = "user_group_custom[" . $fieldId . "]";

		switch ($field["type"]) {
			case UserGroupCustomSearchFieldUtil :: TYPE_STRING :
				if($hasStyle){
					return "<input type=\"text\" name=\"" . $nameProperty . "\" value=\"" . $value . "\" style=\"width:100%;\">";
				}else{
					return "<input type=\"text\" name=\"" . $nameProperty . "\" value=\"" . $value . "\">";
				}

			case UserGroupCustomSearchFieldUtil :: TYPE_TEXTAREA :
				if($hasStyle){
					return "<textarea name=\"" . $nameProperty . "\" style=\"width:100%;\">" . $value . "</textarea>";
				}else {
					return "<textarea name=\"" . $nameProperty . "\">" . $value . "</textarea>";
				}

			case UserGroupCustomSearchFieldUtil :: TYPE_RICHTEXT :
				return "<textarea class=\"custom_field_textarea mceEditor\" name=\"" . $nameProperty . "\">" . $value . "</textarea>";

			case UserGroupCustomSearchFieldUtil :: TYPE_INTEGER :
			case UserGroupCustomSearchFieldUtil :: TYPE_RANGE :
				return "<input type=\"number\" name=\"" . $nameProperty  ."\" value=\"" . $value . "\">";

			case UserGroupCustomSearchFieldUtil :: TYPE_CHECKBOX :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$chks = array();//valuesを配列化
					if(isset($value)){
						$chks = (is_array($value)) ? $value : explode(",", $value);
					}
					$options = explode("\n", $field["option"]);
					foreach ($options as $option) {
						$oVal = trim($option);
						if (in_array($oVal, $chks)) {
							$html[] = "<label><input type=\"checkbox\" name=\"" . $nameProperty . "[]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"checkbox\" name=\"" . $nameProperty . "[]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}
				return implode("\n", $html);

			case UserGroupCustomSearchFieldUtil :: TYPE_RADIO :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					foreach ($options as $option) {
						$oVal = trim($option);
						if (isset($value) && $oVal === $value) {
							$html[] = "<label><input type=\"radio\" name=\"" . $nameProperty . "\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
						} else {
							$html[] = "<label><input type=\"radio\" name=\"" . $nameProperty . "\" value=\"" . $oVal . "\">" . $oVal . "</label>";
						}
					}
				}

				return implode("\n", $html);

			case UserGroupCustomSearchFieldUtil :: TYPE_SELECT :
				$html = array();
				if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
					$options = explode("\n", $field["option"]);
					$html[] = "<select name=\"" . $nameProperty . "\">";
					$html[] = "<option value=\"\"></option>";
					foreach ($options as $option) {
						$oVal = trim($option);
						if (isset($value) && $oVal === $value) {
							$html[] = "<option value=\"" . $oVal . "\" selected=\"selected\">" . $oVal . "</option>";
						} else {
							$html[] = "<option value=\"" . $oVal . "\">" . $oVal . "</option>";
						}
					}
					$html[] = "</select>";

				}

				return implode("\n", $html);
		}
	}

	public static function buildSearchConditionForm($fieldId, $field, $cnd) {
		$form = self::buildForm($fieldId, $field);
		$form = str_replace("user_custom_search", "search_condition", $form);

		switch($field["type"]){
			case UserCustomSearchFieldUtil :: TYPE_TEXTAREA :
			case UserCustomSearchFieldUtil :: TYPE_RICHTEXT :
				if(strpos($form, "mceEditor")){
					$form = str_replace(" mceEditor", "", $form);
				}
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("</textarea>", $cnd[$fieldId] . "</textarea>", $form);
				}
				break;

			case UserCustomSearchFieldUtil :: TYPE_CHECKBOX:
				$forms = explode("\n", $form);
				if(!count($forms)) break;
				$fs = array();
				foreach($forms as $f){
					preg_match('/value="(.*)"/', $f, $tmp);
					if(is_array($cnd[$fieldId]) && in_array($tmp[1], $cnd[$fieldId])){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" checked=\"checked\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
				break;
			case UserCustomSearchFieldUtil :: TYPE_RADIO:
				$forms = explode("\n", $form);
				if(!count($forms)) break;
				$fs = array();
				foreach($forms as $f){
					preg_match('/value="(.*)"/', $f, $tmp);
					if($tmp[1] ==  $cnd[$fieldId]){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" checked=\"checked\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
			case UserCustomSearchFieldUtil :: TYPE_SELECT:
				$forms = explode("\n", $form);
				if(!count($forms)) break;
				$fs = array();
				foreach($forms as $f){
					preg_match('/value="(.*)"/', $f, $tmp);
					if($tmp[1] ==  $cnd[$fieldId]){
						$f = str_replace("value=\"" . $tmp[1] . "\"", "value=\"" . $tmp[1] . "\" selected=\"selected\"", $f);
						$fs[] = $f;
					}else{
						$fs[] = $f;
					}
				}
				$form = implode("\n", $fs);
				break;
			default:
				if(isset($cnd[$fieldId]) && strlen($cnd[$fieldId])){
					$form = str_replace("value=\"\"", "value=\"" . htmlspecialchars($cnd[$fieldId], ENT_QUOTES, "UTF-8") . "\"", $form);
				}
		}

		return $form;
	}
}
