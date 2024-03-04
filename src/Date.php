<?php

namespace Bosromania\PackageCommonTools;

final class Date
{
    static function readableDateDMY (int $time, bool $hour = true, bool $forceYear = false, string $dateSeparator = '/'): string
    {
        if (date('Ymd') == date('Ymd', $time)) {
            $format = "\\t\o\d\a\y" . ($hour ? " H:i" : '');
        }
        else if (date('Ymd', strtotime("-1 day")) == date('Ymd', $time)) {
            $format = "\y\\e\s\\t\\e\\r\d\a\y" . ($hour ? " H:i" : '');
        }
        else {
            $format = 'd'.$dateSeparator.'m';

            if ($forceYear || date('Y', $time) != date('Y')) {
                $format .= $dateSeparator.'Y';
            }

            // append hour, if not older that 3 days
            if ($hour && (time() - $time) < 3*86400) {
                $format .= " H:i";
            }
        }

        return date($format, $time);
    }

    static function readableDateDFY (int $time, bool $hour = true, bool $forceYear = false, string $dateSeparator = ' '): string
    {
        if (date('Ymd') == date('Ymd', $time)) {
            $format = "\\t\o\d\a\y" . ($hour ? " H:i" : '');
        }
        else if (date('Ymd', strtotime("-1 day")) == date('Ymd', $time)) {
            $format = "\y\\e\s\\t\\e\\r\d\a\y" . ($hour ? " H:i" : '');
        }
        else {
            $format = 'd'.$dateSeparator.'F';

            if ($forceYear || date('Y', $time) != date('Y')) {
                $format .= $dateSeparator.'Y';
            }

            // append hour, if not older that 3 days
            if ($hour && (time() - $time) < 3*86400) {
                $format .= " H:i";
            }
        }

        return date($format, $time);
    }
}
