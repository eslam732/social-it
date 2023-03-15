<?php

use App\Models\ChatUsers;
use Illuminate\Support\Facades\Validator;

function testChat($n)
{
    dd($n);
}
function sendMessageRules()
{

    $rules = [
        'chatId' => 'required|numeric',
        'message' => 'required|min:1',

    ];
    $validation = Validator::make(request()->all(), $rules);

    if ($validation->fails()) {
        return $validation = $validation->errors();
    }

}

function createGroupRules()
{

    $rules = [
        'participants_id' => 'required|array',
        'participants_id.*' => 'numeric',
        'name' => 'required|min:5',

    ];
    $validation = Validator::make(request()->all(), $rules);

    if ($validation->fails()) {
        return $validation = $validation->errors();
    }

}


