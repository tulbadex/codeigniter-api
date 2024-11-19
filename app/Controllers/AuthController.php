<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;

helper('jwt_helper');
helper('email_helper');

class AuthController extends ResourceController
{

    public function register()
    {
        $rules = [
            'username' => 'required|is_unique[users.username]',
            'password' => 'required|min_length[8]',
            'email' => 'required|valid_email|is_unique[users.email]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $userModel = new UserModel();
        $userData = [
            'username' => $this->request->getVar('username'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'email' => $this->request->getVar('email'),
            'role' => 'user' // Set the default role here
        ];

        // Insert user data and handle errors
        if (!$userModel->insert($userData)) {
            return $this->fail($userModel->errors());
        }

        return $this->respondCreated(['message' => 'User registered successfully']);
    }

    public function login()
    {
        $userModel = new UserModel();
        $user = $userModel->where('email', $this->request->getVar('email'))->first();

        if ($user && password_verify($this->request->getVar('password'), $user->password)) {
            $payload = [
                'uuid' => $user->uuid,
                'role' => $user->role,
                'iat' => time(),
                'exp' => time() + 3600,
            ];
            $token = generateJWT($payload);
            return $this->respond(['token' => $token]);
        }

        return $this->failUnauthorized('Invalid credentials');
    }

    // Forgot Password
    public function forgotPassword()
    {
        $email = $this->request->getVar('email');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        $token = bin2hex(random_bytes(16)); // Generate reset token

        $passwordResetModel = new PasswordResetModel();
        $passwordResetModel->insert([
            'email' => $email,
            'token' => $token,
        ]);

        // Get the referer domain 
        $referer = $_SERVER['HTTP_REFERER'] ?? ''; 
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://"; 
        $domain = parse_url($referer, PHP_URL_HOST) ?: 'your-domain.com'; 
        
        // Construct the reset password link 
        $resetLink = $protocol . $domain . '/reset-password?token=' . $token;

        // Load the email library 
        $emailService = \Config\Services::email();

        // Set email parameters 
        $emailService->setTo($email); 
        // $emailService->setFrom('tulbadex@gmail.com', 'Book Store');
        $emailService->setSubject('Password Reset Request'); 
        // $message = "Please click the following link to reset your password: <a href='$resetLink'>Reset Password</a>";
        $emailService->setMessage("Please click the following link to reset your password: <a href='$resetLink'>Reset Password</a>"); 
        // Send the email 
        if (!$emailService->send()) { 
            log_message('error', $emailService->printDebugger(['headers']));
            return $this->fail('Failed to send email'); 
        }

        // Send email (for now, we'll just return the token for testing)
        return $this->respond(['message' => 'Password reset link sent', 'token' => $token]);
    }

    // Reset Password
    public function resetPassword()
    {
        $email = $this->request->getVar('email');
        $token = $this->request->getVar('token');
        $newPassword = $this->request->getVar('password');

        $passwordResetModel = new PasswordResetModel();
        $resetRecord = $passwordResetModel->where('email', $email)->where('token', $token)->first();

        if (!$resetRecord) {
            return $this->failNotFound('Invalid reset token');
        }

        $userModel = new UserModel();
        $userModel->where('email', $email)->set([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ])->update();

        $passwordResetModel->where('email', $email)->delete(); // Delete the reset record

        return $this->respond(['message' => 'Password has been reset successfully']);
    }

    // Update Password (Authenticated Users Only)
    public function updatePassword()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return $this->failUnauthorized('Missing Authorization Header');
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = decodeJWT($token);

        if (!$decoded) {
            return $this->failUnauthorized('Invalid Token');
        }

        $userId = $decoded->uuid;
        $currentPassword = $this->request->getVar('current_password');
        $newPassword = $this->request->getVar('new_password');

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user || !password_verify($currentPassword, $user->password)) {
            return $this->fail('Current password is incorrect');
        }

        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        return $this->respond(['message' => 'Password updated successfully']);
    }
}
