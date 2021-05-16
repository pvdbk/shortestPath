<?php

namespace Utils;

Trait Jsons
{
    use \Utils\Arrays;

    public static function isJson(mixed $value): ?bool
    {
        $recIsJson = function(mixed &$toTest, array $refs = []) use(&$recIsJson): ?bool {
            $toTestRefId = \ReflectionReference::fromArrayElement([&$toTest], 0)->getId();
            if (is_null($toTest) || is_bool($toTest) || is_int($toTest) || is_float($toTest) || is_string($toTest)) {
                $ret = true;
            } elseif (in_array($toTestRefId, $refs)) {
                $ret = null;
            } else {
                if (self::isSequential($toTest)) {
                    $array =& $toTest;
                } elseif (is_a($toTest, 'stdClass')) {
                    $array = [];
                    foreach (array_keys(get_object_vars($toTest)) as $key) {
                        $array[$key] =& $toTest->$key;
                    }
                } else {
                    $array = null;
                    $ret = false;
                }
                if(!is_null($array)) {
                    $newRefs = [...$refs, $toTestRefId];
                    $keys = array_keys($array);
                    $ret = true;
                    for ($i = 0; $ret && $i < count($array); $i++) {
                        $ret = $recIsJson($array[$keys[$i]], $newRefs);;
                    }
                }
            }
            return $ret;
        };
        return $recIsJson($value);
    }

    public static function copyJson(mixed $toCopy): mixed
    {
        if (is_null($toCopy) || is_bool($toCopy) || is_int($toCopy) || is_float($toCopy) || is_string($toCopy)) {
            $ret = $toCopy;
        } elseif (self::isSequential($toCopy)) {
            $ret = array_map('self::copyJson', $toCopy);
        } elseif (is_a($toCopy, 'stdClass')) {
            $retToArray = [];
            foreach ($toCopy as $key => $value) {
                $retToArray[$key] = self::copyJson($value);
            }
            $ret = (object) $retToArray;
        } else {
            $ret = null;
        }
        return $ret;
    }

    public static function jsonEquality(mixed $json1, mixed $json2): bool
    {
        if (is_null($json1) || is_bool($json1) || is_int($json1) || is_float($json1) || is_string($json1)) {
            $ret = $json1 === $json2;
        } else {
            if (self::isSequential($json1)) {
                $typesMatch = self::isSequential($json2);
                [$array1, $array2] = [$json1, $json2];
                $testItems = function(mixed $item, int $key) use($array2) {
                    return self::jsonEquality($item, $array2[$key]);
                };
            } elseif (is_a($json1, 'stdClass')) {
                $typesMatch = is_a($json2, 'stdClass');
                [$array1, $array2] = array_map('get_object_vars', [$json1, $json2]);
                $testItems = function(mixed $item, int|string $key) use($array2) {
                    return key_exists($key, $array2) && self::jsonEquality($item, $array2[$key]);
                };
            } else {
                $typesMatch = false;
            }
            $ret = $typesMatch
                && (count($array1) === count($array2))
                && self::every($array1, $testItems, ARRAY_FILTER_USE_BOTH);
        }
        return $ret;
    }
}
