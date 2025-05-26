<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminModel\DefaultProfile;
use App\Models\User;
use App\Models\UserModel\UserDeviceDetail;
use App\Models\UserModel\UserRole;
use App\Models\UserModel\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\BaseController as BaseController;

define('DESTINATIONPATH', 'public/storage/images/');

class UserController extends BaseController
{

    public function index()
    {
        $users = User::all();
        return $this->sendResponse($users, 'Displaying all users data');
    }


    public function updatePassword(Request $request)
    {

        if (Auth::Check()) {

            $validator = \Validator::make(
                $request->all(), [
                    'old_password' => 'required',
                    'password' => 'required|min:6',
                    'password_confirmation' => 'required|same:password',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                
                return response()->json($messages->first(), 422);
                // return redirect()->back()->with('error', $messages->first());
            }

            $objUser = Auth::user();
            $request_data = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['old_password'], $current_password)) {
                $user_id = Auth::User()->id;
                $obj_user = User::find($user_id);
                $obj_user->password = Hash::make($request_data['password']);
                $obj_user->save();

                return $this->sendResponse($objUser, 'Password successfully updated.');
            } else {

                return $this->sendResponse($objUser, 'Please enter correct current password.');
            }
        } else {
            return $this->sendResponse( \Auth::user()->id, 'Something is wrong.');
            
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    // public function updateUserProfile(Request $req)
    // {
    //     try {
    //         if (!Auth::guard('api')->user()) {
    //             return response()->json(['error' => 'Unauthorized', 'status' => 401], 401);
    //         } else {
    //             $id = Auth::guard('api')->user()->id;
    //         }

    //         if (!file_exists(public_path('storage/images'))) {
    //             mkdir(public_path('storage/images'), 0777, true);
    //         }

    //         if ($req->profile) {
    //             if (Str::contains($req->profile, 'storage')) {
    //                 $path = $req->profile;
    //             } else {
    //                 $time = Carbon::now()->timestamp;
    //                 $imageName = 'profile_' . $id;
    //                 $path = DESTINATIONPATH . $imageName . $time . '.png';
    //                 File::delete(Auth::guard('api')->user()->profile);
    //                 file_put_contents($path, base64_decode($req->profile));
    //             }
    //         } else {
    //             $path = null;
    //         }
    //         $data = array(
    //             'profile' => $path,
    //         );
    //         DB::table('users')->where('id', '=', $id)->update($data);
    //         return response()->json([
    //             'status' => 200,
    //             "message" => "Update Profile Successfully",
    //         ], 200);
    //     } catch (\Exception$e) {
    //         return response()->json([
    //             'error' => false,
    //             'message' => $e->getMessage(),
    //             'status' => 500,
    //         ], 500);
    //     }
    // }

   public function updateUserProfile(Request $request)
{
    try {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized', 'status' => 401], 401);
        }

        $id = $user->id;
        $profilePath = $user->profile; // Default to existing

        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('/user/profile'), $filename);
            $profilePath = '/user/profile/' . $filename;

            // Update user profile path in database
            DB::table('users')->where('id', $id)->update(['profile' => $profilePath]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Profile updated successfully',
            'profile_url' => $profilePath,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}

public function forgotPassword(Request $request)
{
    // Validate request
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // Fetch user
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Store OTP in password_resets table
    DB::table('password_reset_otps')->updateOrInsert(
        ['email' => $request->email],
        [
            'otp' => $otp,
            'created_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(3) // Correct expiry
        ]
    );

    // Send OTP via email
    Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
        $message->to($request->email)
                ->subject('Password Reset OTP');
    });

    $token = JWTAuth::fromUser($user);

    return response()->json([
        'email' => $request->email,
        'message' => 'OTP sent successfully',
        'token' => $token, // Only valid if Sanctum is used
        'type' => 'bearer'
    ], 200);
}

    public function verifyOtp(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        // 'email' => 'required|email|exists:password_reset_otps,email',
        'otp' => 'required|digits:6'
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()], 400);
    }

    // Fetch OTP record
    $otpRecord = DB::table('password_reset_otps')
    // ->where('email', $request->email)
                                 ->where('otp', $request->otp)
                                 ->first();

    // Check if OTP exists
    if (!$otpRecord) {
        return response()->json(['message' => 'Invalid OTP.'], 400);
    }

    // Check if OTP is expired
    if (Carbon::now()->greaterThan($otpRecord->expires_at)) {
        return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
    }

    // Mark OTP as verified
    DB::table('password_reset_otps')
    // ->where('email', $request->email)
    ->where('otp', $request->otp)
    ->update(['is_verified' => true]);

    
    return response()->json([
        'message' => 'OTP verified successfully',
        'email' => $otpRecord->email,
        'type' => 'bearer'
    ], 200);


    return response()->json(['message' => 'OTP verified successfully.'], 200);
}


    public function reset(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password',
        
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }


        
    $user = User::where('email', $request->email)->first();
    if(!empty($user)){
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['email'=>$request->email, 'message'=>'Password has been reset successfully']);
    } else {

        return response()->json(['email'=>$request->email, 'message'=>'Please enter correct current email.']);
    }


    // return $status === Password::PASSWORD_RESET
    //     ? response()->json(['message' => 'Password has been reset successfully.'])
    //     : response()->json(['message' => 'Failed to reset password.'], 500);
}

}
