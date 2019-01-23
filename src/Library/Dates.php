<?php

namespace Abrahamf24\PlansSubscriptions\Library;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class Dates{

	/**
	 * Agrega la cantidad de periodos indicados a una fecha
	 * 
	 * @param mixed  $date         Cualquier parametro que acepte new Carbon()
	 * @param int    $periods      Cantidad de periodos a agregar
	 * @param int    $period_count Cantidad que tiene un periodo
	 * @param string $period_unit  Unidad en la que está el periodo
	 */
	public static function addPeriods($date, int $periods, $period_count, $period_unit){
		if(!in_array($period_unit, ['day','month'])){
			throw new \Exception("Unidad de periodo no permitida", 1);
		}

		if($period_count<=0){
			throw new \Exception("period_count debe ser mayor a cero", 1);
		}

		if($periods<=0){
			throw new \Exception("Los periodos deben ser mayor a cero", 1);
		}

		$date = new Carbon($date);
		if($period_unit=='day'){
			$date->addDays($periods*$period_count);
			return $date;
		}

		$date = self::addMonthsWithoutOverflow($date, $periods*$period_count);

		return $date;
	}

	private static function addMonthsWithoutOverflow($date, $months){
		$date = new Carbon($date);
		$testDate = Carbon::create($date->year, 1, 1);

		$newMonth = $date->month+$months;
		$testDate->month = $newMonth;
		$maxDay = $testDate->daysInMonth;

		$newDay = $date->day>$maxDay?$maxDay:$date->day;

		$date->day = $newDay;
		$date->month = $newMonth;

		return $date;
	}
}