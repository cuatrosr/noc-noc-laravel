<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Customs\Services\EmailVerificationService;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(private EmailVerificationService $service)
    {
        $this->middleware('auth:api', ['except' => ['login', 'resendEmailVerificationLink', 'verifyUserEmail', 'changeUserPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $token = Auth::attempt($request->validated());
        if ($token) {
            return $this->respondWithToken($token);
        }
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    /**
     * Resend the email verification link.
     */
    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {
        return $this->service->resendLink($request->email);
    }

    /**
     * Verify the user's email.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->service->verifyEmail($request->email, $request->token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegistrationRequest $request)
    {
        $userData = $request->validated();
        $password = $request->password;
        $role = $userData['role'];
        unset($userData['role']);
        $user = User::create($userData);
        if ($user) {
            $user->assignRole($role);
            $this->service->sendVerificationLink($user, $password);
            $token = auth()->login($user);
            return $this->respondWithToken($token);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Registration failed'], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $firstRoleName = auth()->user()->roles->first()->name;
        $userData = auth()->user()->toArray();
        $userData['role'] = $firstRoleName;
        unset($userData['roles']);
        return response()->json($userData);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Change the user's password.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUserPassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        $password = $user->password;
        $passwordCheck = $this->validateCurrentPassword($data['current_password'], $password);
        if ($passwordCheck === false) {
            return response()->json(['status' => 'error', 'message' => 'Current password is incorrect'], 401);
        }
        $user->password = Hash::make($data['password']);
        $user->save();
        return response()->json(['status' => 'success', 'message' => 'Password changed successfully'], 200);
    }

    /**
     * Validate the current password.
     * 
     * @param string $current_password
     * @return bool
     */
    private function validateCurrentPassword($current_password, $password)
    {
        if (password_verify($current_password, $password)) return true;
        return false;
    }
}
