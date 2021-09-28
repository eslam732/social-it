<?php

namespace App\Http\Controllers;

use App\Mail\VerifyAcount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $user = Auth::user();
        $user = User::find($user->id);
        if ($user->verified) {
            return response()->json('user is alredy verified', 200);
        }

        if (request()->verificationCode) {
            $verificationCode = request()->verificationCode;

            if ($user->verification_code == $verificationCode) {$user->verified = true;

                $user->save();
                return response()->json('verified', 200);
            } else {
                return response()->json('wrong verification code ', 200);
            }
        }
        $verificationCode = Str::random(6);
        $user->verification_code = $verificationCode;
        $user->save();

        Mail::to($user)->send(new VerifyAcount($user));
        return response()->json('verificarion code sent', 200);
    }

    public function verifyFromEmail($id)
    {
        $user = User::find($id);
        $user->verified = true;

        $user->save();

        return response()->json('verified',200);

    }
}
