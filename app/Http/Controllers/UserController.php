<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cart;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public $id;
    public function __construct()
    {
        $this->id = null;
        if(auth()->user() != null) {
            $this->id = auth()->user()->id;
        }
    }

    public function login (LoginRequest $request)
    {
        $object = $request->user;
        $credentials = ['phone' => $object['phone'], 'password' => $object['password']];
        if( ! $token =  auth()->attempt($credentials)) {
            return response()->json([
                'message' => [
                    'error' => 'Số điện thoại hoặc mật khẩu không đúng!'
                ]
            ], 401);
        }

        $user = auth()->user();

        return response()->json(['token' => $token, 'user' => $user, 'notifications' => $user->unreadNotifications]);
    }

    public function register(RegisterRequest $request) {
        $controller = new Controller();
        $image = $controller->show_images(['logo_no_background.png']);
       $user =  User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>bcrypt( $request->password),
            'location' => $request->location,
            'avatar' => $image
        ]);

        $credentials = request(['phone', 'password']);
        if( ! $token =  auth()->attempt($credentials)) {
            return response()->json(['error' => 'unthorized'], 401);
        }
        $user = auth()->user();
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout() {
        auth('api')->logout();
        return response()->json(["message" => "Logout"]);
    }

    public function me() {
        $user = auth('api')->user();
        $notifications = $user->unreadNotifications->pluck('data');
        return response()->json([
            'user' => auth('api')->user(),
            'notifications' => $notifications,
            'vouchers' => $user->vouchers,
        ]);
    }

    public function update(UpdateUserRequest $request) {
        $file_names = [];
        $filenames = [];
        $user = null;

        if($request->hasFile('avatar')) {
            $filenames = parent::upload_images($request->file('avatar'), $file_names);
            $file_names = parent::show_images($filenames);
            $user = User::find(auth('api')->id())->update([
                'name' => $request->name,
                'email' => $request->email,
                'location' => $request->location,
                'avatar' => $file_names
            ]);
        }
        else {
            $user = User::find(auth('api')->id())->update([
                'name' => $request->name,
                'email' => $request->email,
                'location' => $request->location,
            ]);
        }
        $user = User::where('id', auth('api')->id())->first();
        return parent::success_create_update($user);
    }

    public function resetPassword(Request $request) {
        $old = $request->old;
        $new = $request->new;
        $id = auth('api')->id();
        $user = User::find($id);

        if (Hash::check($old, auth('api')->user()->password)) {
           $user->update([
                'password' => bcrypt($new),
            ]);
            $token = JWTAuth::parseToken()->refresh();
            $user->notify(new ResetPasswordNotification($token));

            return response()->json(['message' => $new, 'user' => $user, 'token' => $token], 200);
        }
        else {
            return response()->json(['message' => 'Mật khẩu không đúng!']);
        }
    }

    public function forgetPassword (Request $request) {
        $email = $request->email;
        $phone = $request->phone;
        $user = User::where('email', $email)->where('phone', $phone)->first();
        if ($user) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 8; $i++) {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
            $user->update([
                'password' => bcrypt($randomString),
            ]);
            $user->notify(new ResetPasswordNotification($randomString));
            return response()->json(['message' => 'Chúng tôi đã gửi mật khẩu mới vào email của bạn, vui lòng kiểm tra email !']);
        }
        else
            return response()->json(['message' => 'Email hoặc số điện thoại không khớp!'], 403);
    }

    public function get_notifications() {
        $user = auth('api')->user();
        $notifications = $user->unreadNotifications;

        return parent::get_list($notifications);
    }

}
