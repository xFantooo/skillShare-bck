<?php

declare(strict_types=1);

namespace App\services;

class MailService
{
    public static function sendVerificationEmail(string $email, string $token): void
    {
        $link = "http://localhost:3000/verify-email?token=" . $token;
        $subject = "🔐 Please verify your email address";

        $message = "
        <html>
        <head>
            <title>Verify Your Email</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <table cellpadding='0' cellspacing='0' width='100%' style='background-color: #f4f4f4; padding: 40px 0;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
                            <tr>
                                <td style='background-color: #007bff; padding: 20px; text-align: center; color: #fff;'>
                                    <h1 style='margin: 0; font-size: 24px;'>SkillShare</h1>
                                    <p style='margin: 5px 0 0;'>Confirm Your Email Address</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 30px; color: #333333; font-size: 16px;'>
                                    <p style='margin-top: 0;'>Hi there 👋,</p>
                                    <p>Thank you for creating an account with <strong>SkillShare</strong>.</p>
                                    <p>To complete your registration, please verify your email address by clicking the button below:</p>
                                    <p style='text-align: center; margin: 30px 0;'>
                                        <a href='{$link}' style='background-color: #28a745; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify Email</a>
                                    </p>
                                    <p>If the button above doesn't work, copy and paste the following link into your browser:</p>
                                    <p style='word-break: break-all; color: #555;'>{$link}</p>
                                    <p>If you didn’t create this account, you can safely ignore this email.</p>
                                    <p style='margin-bottom: 0;'>Warm regards,<br>The SkillShare Team</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='background-color: #f1f1f1; text-align: center; padding: 20px; font-size: 12px; color: #999999;'>
                                    © " . date('Y') . " SkillShare. All rights reserved.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        // Proper headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: SkillShare <noreply@SkillShare.com>\r\n";

        // Send the email
        mail($email, $subject, $message, $headers);
    }
}