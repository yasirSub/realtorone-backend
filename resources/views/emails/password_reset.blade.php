<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #667eea; text-align: center;">Reset Your Password</h2>
        <p>Hello,</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" style="background-color: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Reset Password</a>
        </div>
        <p>If you prefer, you can use the following token directly in the app:</p>
        <p style="text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; background: #f4f4f4; padding: 10px; border-radius: 5px;">{{ $token }}</p>
        <p>This password reset link will expire in 60 minutes.</p>
        <p>If you did not request a password reset, no further action is required.</p>
        <p>Regards,<br>Realtor One Team</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 11px; color: #999;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: <br>{{ $resetUrl }}</p>
    </div>
</body>
</html>
