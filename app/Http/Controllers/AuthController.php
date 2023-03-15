<?php

namespace App\Http\Controllers;

use App\Mail\SendResetPasswordCode;
use App\Mail\VerifyAcount;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function signUp()
    {

        $validation = signUpRules();
        if ($validation) {
            return $validation;
        }
        $data = request()->all();
        $path = request()->file('picture')->store('profilePictures');
        $data['picture'] = $path;
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        $resArr = [];
        $resArr['token'] = $user->createToken('api-application')->accessToken;
        $resArr['name'] = $user->name;
        $resArr['id'] = $user->id;

        return response()->json($resArr, 200);
    }
    public function login()
    {
        $validation = loginRules();
        if ($validation) {
            return $validation;
        }

        $user = User::where('email', request()->email)->first();
        if (!$user) {
            return response()->json(['error' => ' email not found'], 203);
        }

        if (!Hash::check(request()->password, $user->password)) {
            return response()->json(['error' => 'inncorrect password'], 203);
        }

        $resArr = [];
        $resArr['token'] = $user->createToken('api-application')->accessToken;
        $resArr['name'] = $user->name;
        $resArr['email'] = $user->email;
        $resArr['id'] = $user->id;
        return response()->json($resArr, 200);
    }

    public function UnAuthorized()
    {
        return response()->json('UnAuthorized', 203);
    }

    public function verify()
    {
        try {
        $user = Auth::user();
        $user = User::find($user->id);
        if ($user->verified) {
            return response()->json('user is alredy verified', 200);
        }

        if (request()->verificationCode) {
            $verificationCode = request()->verificationCode;

            if ($user->verification_code == $verificationCode) {$user->verified = true;
                $user->verification_code = null;
                $user->save();
                return response()->json('verified', 200);
            } else {
                return response()->json('wrong verification code ', 200);
            }
        }
       
            $code = mt_rand(100000, 999999).Str::random(6);

           
            $user->verification_code = $code;
            Mail::to($user)->send(new VerifyAcount($user));
            $user->save();

        } catch (\Exception $e) {
            return response()->json(['some error has ocured' => $e->getMessage(), 400]);

        }

        return response()->json('verificarion code sent', 200);
    }

    public function verifyFromEmail($id)
    {
        $user = User::find($id);
        $user->verified = true;
        $user->verification_code = null;
        $user->save();

        return response()->json('verified', 200);

    }
    public function forgetPassword()
    {try {
        $validEmail = Validator::make(request()->all(), ['email' => 'required|email|exists:users']);
        if ($validEmail->fails()) {
            return response($validEmail->errors(), 400);
        }
         $data = [];
            $data['email'] = request()->email;
            ResetCodePassword::where('email', request()->email)->delete();
            $data['code'] = mt_rand(100000, 999999) . Str::random(6);
            $codeData = ResetCodePassword::create($data);
            Mail::to(request()->email)->send(new SendResetPasswordCode($codeData->code));
        } catch (\Exception$e) {
            return response()->json(['some error has ocured' => $e->getMessage(), 500]);

        }
        return response(['message' => 'your reset code has been sent to your email'], 200);

    }

    public function checkCode()
    {
        try {
        $validate = Validator::make(request()->all(), ['email' => 'required|email|exists:reset_code_passwords',
            'code' => 'required', 'password' => 'required|confirmed']);
        if ($validate->fails()) {
            return response($validate->errors(), 400);
        }
        $reqdata = request()->all();
        $resetData = ResetCodePassword::where('email', $reqdata['email'])->get();

        if ($reqdata['code'] !== $resetData[0]->code) {
            return response()->json(['inncorect code'], 400);
        }

        if (strtotime($resetData[0]->created_at->addHours(1)) < strtotime(now())) {

            $resetData->each->delete();
            return response(['message' => trans('passwords.code_is_expire')], 422);
        }

        $password = bcrypt($reqdata['password']);
        User::where('email', $reqdata['email'])->update(['password' => $password]);
        $resetData->each->delete(); 
           
        } catch (\Exception $e) {
            return response()->json(['some error has ocured' => $e->getMessage(), 400]);

        }
        return response()->json(['password reseted successfully'], 200);

    }
    
}
