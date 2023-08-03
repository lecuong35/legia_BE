<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $id = '';
    public function __construct()
    {
        if(auth()->guard('admin')->user() != null) {
            $this->id = auth()->guard('admin')->user()->id;
        }
    }

    public function login (AdminLoginRequest $request)
    {
        $object = $request->admin;
        $credentials = ['email' => $object['email'], 'password' => $object['password']];
        if( ! $token =  auth()->guard('admin')->attempt($credentials)) {
            return response()->json([
                'message' => [
                    'error' => 'Email hoặc mật khẩu không đúng!'
                ]
            ], 401);
        }

        return response()->json(
            [
                'token' => $token,
                'admin' => auth('admin')->user(),
                'notifications' => json_encode(auth('admin')->user()->notifications[0]->data)
            ], 200
        );
    }

    public function register(Request $request) {
       $admin =  Admin::create([
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>bcrypt( $request->password),
            'location' => $request->location,
            'name' => $request->name,
            'created_at' => now(),
            'updated-at' => now()
        ]);

        return $admin;

    }

    public function logout() {
        auth('admin')->logout();

        return response()->json(['message' => 'successfully logged'], 200);
    }

    public function update(UpdateAdminRequest $request) {
       DB::table('admins')
           ->where('id','=',$this->id)
           ->update([
            'phone' => $request->phone,
            'email' => $request->email,
            'bank' => $request->bank,
            'facebook' => $request->facebook,
            'password' =>bcrypt( $request->password),
            'updated_at' => now()
           ]);

        $admin = DB::table('admins')
                ->where('id','=',$this->id)
                ->get();
        return response()->json($admin);
    }

    public function get_notifications() {
        $admin = auth('admin')->user();
        $notifications = $admin->unreadNotifications;

        return parent::get_list($notifications);
    }

}
