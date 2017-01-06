<?php

namespace GDCE\LaravelDreamfactory;

/*
 * GDCE customs helper functions
 */



use Carbon\Carbon;

class GDCE
{
    public function val($value, $valueIfNotExist=''){
        return isset($value)?$value:$valueIfNotExist;
    }

    public function num($val, $precision=2){
        try{
            return number_format($val, $precision, '.', ',');
        }catch (\Exception $e){
            return $e;
        }
    }

    public function date($date, $originFormat='Y-m-d H:i:s', $targetFormat='d/m/Y'){
        try{
            return Carbon::createFromFormat($originFormat,$date)->format($targetFormat);
        }catch (\Exception $e){
            return $e;
        }
    }
}
