<?php

use App\Models\Ship;
use Illuminate\Support\Str;

function generate_code($user_id)
{
    $date = now()->format('Y-m-d');
    $str = strtoupper(Str::random(6));
    $code = $str . $user_id . '-' . $date;

    $ship = Ship::where('code', $code)->first();
    if(isset($ship)){
        generate_code($user_id);
    }else{
        return $code;
    }
}
