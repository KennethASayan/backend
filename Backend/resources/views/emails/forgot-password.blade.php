<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .container {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <p>Please click the button below to reset your password:</p>
        
        <a href="{{ env('FRONTEND_URL') }}/reset-password?token={{ $token }}" class="button">
            Reset Password
        </a>

        <p style="margin-top: 20px;">If you did not request a password reset, no further action is required.</p>
        
        <p>This password reset link will expire in 60 minutes.</p>
        
        <p>If you're having trouble clicking the button, copy and paste this URL into your browser:</p>
        <p>{{ env('FRONTEND_URL') }}/reset-password?token={{ $token }}</p>
        
        <p style="margin-top: 30px;">Regards,<br>Document Monitoring System</p>
    </div>
</body>
</html>