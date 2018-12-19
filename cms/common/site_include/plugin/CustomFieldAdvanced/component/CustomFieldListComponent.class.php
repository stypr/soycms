<?php

class CustomFieldListComponent extends HTMLList {

	private $labels = array();

	function populateItem($entity, $i){
		static $i = 0;
		$i++;

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text"=>$entity->getLabel(),
			"id" => "label_text_" . $i,
		));

		$this->addLabel("id", array(
			"text"=> $entity->getId(),
		));

		$this->addLabel("type", array(
			"text"=> (isset(CustomField::$TYPES[$entity->getType()])) ? CustomField::$TYPES[$entity->getType()] : "",
			"id" => "type_text_" . $i,
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:id="'.$entity->getId().'"'
		));


		/* カスタムフィールド設定変更用 */
		$this->addLink("toggle_update", array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_'.$i.'\').show();' .
						'$(\'#label_text_'.$i.'\').hide();' .
						'$(\'#type_select_'.$i.'\').show();' .
						'$(\'#type_text_'.$i.'\').hide();' .
						'$(\'#update_link_'.$i.'\').show();' .
						'$(this).hide();'
		));

		$this->addLink("update_link", array(
			"link" => "javascript:void(0)",
			"id" => "update_link_" . $i,
			"onclick" => '$(\'#update_submit_'.$i.'\').click();' .
						'return false;'
		));

		$this->addLink("update_submit", array(
			"name" => "update_submit",
			"value" => $entity->getId(),
			"id" => "update_submit_".$i
		));

		$this->addInput("label_input", array(
			"name" => "label",
			"id" => "label_input_" . $i,
			"value" => $entity->getLabel(),
		));

		$this->addSelect("type_select", array(
			"name" => "type",
			"options" => CustomField::$TYPES,
			"id" => "type_select_" . $i,
			"selected" => $entity->getType(),
		));

		/* 順番変更用 */
		$this->addInput("field_id", array(
			"name" => "field_id",
			"value" => $entity->getId(),
		));


		/* 削除用 */
		$this->addInput("delete_submit", array(
			"name" => "delete_submit",
			"value" => $entity->getId(),
			"id" => "delete_submit_".$i
		));

		$this->addLink("delete", array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"'.$entity->getLabel().'\"?")){$(\'#delete_submit_'.$i.'\').click();}return false;'
		));

		/* 高度な設定 */
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_'.$i.'\').toggle();',
			"style" => (!$entity->getShowInput() OR $entity->getLabelId() OR $entity->getDefaultValue() OR $entity->getEmptyValue() OR $entity->getDescription()) ? "background-color:yellow;" : ""
		));

		$this->addModel("field_config", array(
			"id" => "field_config_" . $i
		));

		//表示の切り替え：表示/非表示/ラベルと連動
		$this->addCheckBox("editer_show", array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_YES,
			"selected" => $entity->getShowInput() && strlen($entity->getLabelId())==0,
			"label" => "常に表示",
		));
		$this->addCheckBox("editer_hide", array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_NO,
			"selected" => !$entity->getShowInput(),
			"label" => "常に隠す",
		));
		$this->addCheckBox("editer_label", array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_LABEL,
			"selected" => strlen($entity->getLabelId()),
			"label" => "ラベルと連動",
		));
		$this->addSelect("labels", array(
			"options" => $this->labels,
			"property" => "caption",
			"name" => "config[labelId]",
			"selected" => $entity->getLabelId(),
		));

		$this->addInput("default_value", array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->addCheckBox("empty_hide", array(
			"name" => "config[hideIfEmpty]",
			"value" => 1,
			"selected" => $entity->getHideIfEmpty(),
			"label" => "表示しない",
		));
		$this->addCheckBox("empty_show", array(
			"name" => "config[hideIfEmpty]",
			"value" => 0,
			"selected" => !$entity->getHideIfEmpty(),
			"label" => "指定の値を出力",
		));
		$this->addInput("empty_value", array(
			"name" => "config[emptyValue]",
			"value" => $entity->getEmptyValue()
		));

		$this->addInput("output", array(
			"name" => "config[output]",
			"value" => $entity->getOutput()
		));

		$this->addModel("use_extra", array(
			"visible" => $entity->hasExtra(),
		));

		$this->addTextArea("extra_outputs", array(
			"name" => "config[extraOutputs]",
			"value" => $entity->getExtraOutputs(),
		));

		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		$this->addInput("description", array(
			"name" => "config[description]",
			"value" => $entity->getDescription()
		));

		$this->addModel("with_options", array(
			"visible" => $entity->hasOption()
		));

		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_'.$i.'\').click();return false;'
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $entity->getId(),
			"id" => "update_advance_submit_".$i
		));
	}

	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}
}
