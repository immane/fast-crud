<?php

namespace RinProject\FastCrudBundle\Utils;

class Math
{
    // constrain
    const M_E = 2.7182818284590452354;
    const M_EULER = 0.57721566490153286061;
    const M_LNPI = 1.14472988584940017414;
    const M_LN2 = 0.69314718055994530942;
    const M_LN10 = 2.30258509299404568402;
    const M_LOG2E = 1.4426950408889634074;
    const M_LOG10E = 0.43429448190325182765;
    const M_PI = 3.14159265358979323846;
    const M_PI_2 = 1.57079632679489661923;
    const M_PI_4 = 0.78539816339744830962;
    const M_1_PI = 0.31830988618379067154;
    const M_2_PI = 0.63661977236758134308;
    const M_SQRTPI = 1.77245385090551602729;
    const M_2_SQRTPI = 1.12837916709551257390;
    const M_SQRT1_2 = 0.70710678118654752440;
    const M_SQRT2 = 1.41421356237309504880;
    const M_SQRT3 = 1.73205080756887729352;

    // common
    public static function abs($x) { return abs($x); }
    public static function acos($x) { return acos($x); }
    public static function acosh($x) { return acosh($x); }
    public static function asin($x) { return asin($x); }
    public static function asinh($x) { return asinh($x); }
    public static function atan($x) { return atan($x); }
    public static function atan2($y, $x) { return atan2($y, $x); }
    public static function atanh($x) { return atanh($x); }
    public static function base_convert($number, $frombase, $tobase) { return base_convert($number,$frombase,$tobase); }
    public static function bindec($x) { return bindec($x); }
    public static function ceil($x) { return ceil($x); }
    public static function cos($x) { return cos($x); }
    public static function cosh($x) { return cosh($x); }
    public static function decbin($x) { return decbin($x); }
    public static function dechex($x) { return dechex($x); }
    public static function decoct($x) { return decoct($x); }
    public static function deg2rad($x) { return deg2rad($x); }
    public static function exp($x) { return exp($x); }
    public static function expm1($x) { return expm1($x); }
    public static function floor($x) { return floor($x); }
    public static function fmod($x, $y) { return fmod($x, $y); }
    public static function getrandmax($x) { return getrandmax($x); }
    public static function hexdec($x) { return hexdec($x); }
    public static function hypot($x, $y) { return hypot($x, $y); }
    public static function is_finite($x) { return is_finite($x); }
    public static function is_infinite($x) { return is_infinite($x); }
    public static function is_nan($x) { return is_nan($x); }
    public static function lcg_value($x) { return lcg_value($x); }
    public static function log($x) { return log($x); }
    public static function log10($x) { return log10($x); }
    public static function log1p($x) { return log1p($x); }
    public static function max($x) { return max($x); }
    public static function min($x) { return min($x); }
    public static function mt_getrandmax($x) { return mt_getrandmax($x); }
    public static function mt_rand($x) { return mt_rand($x); }
    public static function mt_srand($x) { return mt_srand($x); }
    public static function octdec($x) { return octdec($x); }
    public static function pi() { return pi(); }
    public static function pow($x, $y) { return pow($x, $y); }
    public static function rad2deg($x) { return rad2deg($x); }
    public static function rand($x) { return rand($x); }
    public static function round($x) { return round($x); }
    public static function sin($x) { return sin($x); }
    public static function sinh($x) { return sinh($x); }
    public static function sqrt($x) { return sqrt($x); }
    public static function srand($x) { return srand($x); }
    public static function tan($x) { return tan($x); }
    public static function tanh($x) { return tanh($x); }
}
