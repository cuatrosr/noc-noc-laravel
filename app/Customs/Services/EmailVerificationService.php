<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use App\Notifications\EmailVerificationNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class EmailVerificationService
{
    /**
     * Send a verification link to the user.
     * 
     * @param object $user
     * @return void
    */
    public function sendVerificationLink($user, $password)
    {
        Notification::send($user, new EmailVerificationNotification($this->generateVerificationLink($user->email), $password));
    }

    /**
     * Resend the verification link.
     * 
     * @param string $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendLink($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404)->send();
            exit;
        }
        $this->sendVerificationLink($user);
        return response()->json(['status' => 'success', 'message' => 'Verification link sent successfully'], 200)->send();
    }

    /**
     * Check if email is verified.
     * 
     * @param object $user
     */
    public function checkIfEmailIsVerified($user)
    {
        if ($user->email_verified_at) {
            return response()->json(['status' => 'error', 'message' => 'Email already verified'], 400)->send();
            exit;
        }
    }

    /**
     * Verify the email.
     * 
     * @param string $email
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail($email, $token)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            response()->json(['status' => 'error', 'message' => 'User not found'], 404)->send();
            exit;
        }
        $this->checkIfEmailIsVerified($user);
        $verifiedToken = $this->verifyToken($email, $token);
        if ($user->markEmailAsVerified()) {
            $verifiedToken->delete();
            return response()->json(['status' => 'success', 'message' => 'Email verified successfully'], 200)->send();
        } else {
            return response()->json(['status' => 'error', 'message' => 'Email verification failed'], 500)->send();
        }
    }

    /**
     * Verify the token.
     * 
     * @param string $email
     * @param string $token
     */
    public function verifyToken($email, $token)
    {
        $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
        if ($token) {
            if ($token->expires_at < now()) {
                $token->delete();
                response()->json(['status' => 'error', 'message' => 'Token expired'], 400)->send();
                exit;
            } else {
                return $token;
            }
        } else {
            response()->json(['status' => 'error', 'message' => 'Invalid token'], 400)->send();
            exit;
        }
    }

    /**
     * Generate a verification link.
     * 
     * @return string
     */
    public function generateVerificationLink($email)
    {
        $checkIfTokenExists = EmailVerificationToken::where('email', $email)->first();
        if ($checkIfTokenExists) $checkIfTokenExists->delete();
        $token = Str::uuid();
        $url = "http://localhost:5174/verify-email". "?token=$token". "&email=$email";
        $saveToken = EmailVerificationToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addMinutes(60)
        ]);
        if ($saveToken) return $url;
    }
}