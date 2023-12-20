<?php

namespace Bosromania\PackageCommonTools;

final class Date
{
    static function readableDateDMY (int $time, bool $hour = true): string
    {
        if (date('Ymd') == date('Ymd', $time)) {
            $format = "\\t\o\d\a\y" . ($hour ? " H:i" : '');
        }
        else if (date('Ymd', strtotime("-1 day")) == date('Ymd', $time)) {
            $format = "\y\\e\s\\t\\e\\r\d\a\y" . ($hour ? " H:i" : '');
        }
        else {
            $format = "d/m/Y";

            // append hour, if not older that 3 days
            if ((time() - $time) < 3*86400) {
                $format .= " H:i";
            }
        }

        return date($format, $time);
    }

    static function readableDateDFY (int $time, bool $hour = true): string
    {
        if (date('Ymd') == date('Ymd', $time)) {
            $format = "\\t\o\d\a\y" . ($hour ? " H:i" : '');
        }
        else if (date('Ymd', strtotime("-1 day")) == date('Ymd', $time)) {
            $format = "\y\\e\s\\t\\e\\r\d\a\y" . ($hour ? " H:i" : '');
        }
        else {
            $format = "d F Y";

            // append hour, if not older that 3 days
            if ((time() - $time) < 3*86400) {
                $format .= " H:i";
            }
        }

        return date($format, $time);
    }
}
