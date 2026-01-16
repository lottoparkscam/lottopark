<?php
class Banners_Calc
{
    public static function vertical_middle($objectHeight, $mainHeight)
    {
        $calc = round($mainHeight - $objectHeight, 2);
        $calc = round($calc / 2, 2);
        
        return $calc;
    }

    public static function vertical_middle_text($objectHeight, $mainHeight, $fontSize)
    {
        $calc = round($mainHeight - $objectHeight, 2);
        $calc = round($calc / 2, 2);

        return $calc;
    }

    public static function align_middle($objectWidth, $mainWidth)
    {
        $calc = round($mainWidth - $objectWidth, 2);
        $calc = round($calc / 2, 2);

        return $calc;
    }

    public static function align_right($objectWidth, $mainWidth, $margin = 0)
    {
        $calc = round($mainWidth - $objectWidth, 2);
        $calc = round($calc - $margin, 2);

        return $calc;
    }

    public static function align_bottom($objectHeight, $mainHeight, $margin = 0)
    {
        $calc = round($mainHeight - $objectHeight, 2);
        $calc = round($calc - $margin, 2);

        return $calc;
    }

    public static function margin_with_object($objectWidth, $margin)
    {
        $calc = round($objectWidth + $margin, 2);

        return $calc;
    }
}
