<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $userId;
	private $reservedScheduleList = array();
	private $labelList;

	function build($y, $m, $dspOtherMD = false, $dspCaption = false, $dspRegHol = true, $dspMonthLink = false, $isBefore = false, $isNextMonth = false){
		$this->year = $y;
		$this->month = $m;

		$resLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic");

		$this->reservedScheduleList = $resLogic->getReservedScheduleListByUserIdAndPeriod($this->userId, $y, $m);
		$this->labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList(null);

		return parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol, $dspMonthLink, $isBefore, $isNextMonth);
	}

	function handleFunc($i, $cd, $wc, $da, $isOtherMonth){
		$res = self::getReserveArray($i);

		$html = array();
		$html[] = $i;

		//予約がある場合
		if(count($res)){
			foreach($res as $resId => $v){
				$html[] = "<a href=\"" . soyshop_get_mypage_url() . "/reserve/detail/" . $resId . "\" class=\"btn btn-info schedule_button\">" . self::getLabel($v["label_id"]) . "</a>";
			}
		}

		return implode("<br>", $html);
	}

	private function getReserveArray($d){
		return (isset($this->reservedScheduleList[$d])) ? $this->reservedScheduleList[$d] : array();
	}

	private function getLabel($labelId){
		return (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : "";
	}

	function setUserId($userId){
		$this->userId = $userId;
	}
}
