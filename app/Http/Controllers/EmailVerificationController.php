<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    public function email_verification($token)
    {

        // chech if token is valid or not
        $token = Crypt::decryptString($token);
        $user_token = DB::table('activation_user_tokens')->where('token', $token)->first();
        if(!empty($user_token)) {
            // find and update status user
            $user = User::find($user_token->user_id);
            $error = null;
            if($user->status == 'active') {
                $error = 'Your account is active';
            } else {
                $user->markEmailAsVerified();
                $user->update([
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
            }

            // delete user token
            $user_token = DB::table('activation_user_tokens')->where('token', $token)->delete();
        } else {
            $user = null;
            $error = 'invalid token ! activation user failed';
        }

        // return view
        return view('mail.verificationMail', compact('user', 'error'));
    }
}
