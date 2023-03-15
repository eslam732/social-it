<?php
 
 use Illuminate\Support\Facades\Validator;


function test($n)
{
    dd($n);
}

 function signUpRules()
{
    
    $rules = [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'about' => 'required|min:5',
        'picture' => 'required|image',
        'password' => 'required|min:4|confirmed',

    ];
    $validation = Validator::make(request()->all(), $rules);
    
        if ($validation->fails()) {
            return $validation = $validation->errors();
        }
        else return 0;
}

function loginRules()
{
    
    $rules = [
        
        'email' => 'required|email',
        'password' => 'required',

    ];
    $validation = Validator::make(request()->all(), $rules);
    
        if ($validation->fails()) {
            return $validation = $validation->errors();
        }
        else return 0;
}

function tweetRules()
{
    
    $rules = [
        
        'content' => 'required',
       

    ];
    $validation = Validator::make(request()->all(), $rules);
    
        if ($validation->fails()) {
            return $validation = $validation->errors();
        }
        else return 0;
}