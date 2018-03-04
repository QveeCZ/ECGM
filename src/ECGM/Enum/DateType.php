<?php

namespace ECGM\Enum;


abstract class DateType extends BasicEnum
{

    const SECONDS = 1;
    const MINUTES = 60;
    const HOURS = self::MINUTES * 60;
    const DAYS = self::HOURS * 24;
    const  WEEKS = self::DAYS * 7;

}