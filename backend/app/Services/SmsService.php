<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Отправить SMS с кодом подтверждения
     */
    public function sendVerificationCode(string $phone, string $code): bool
    {
        // В режиме разработки просто логируем
        if (config('sms.development_mode')) {
            Log::info("SMS отправлен (режим разработки)", [
                'phone' => $phone,
                'code' => $code,
                'message' => 'В режиме разработки SMS не отправляется'
            ]);
            return true;
        }

        // В продакшене выбираем провайдера
        return match (config('sms.provider')) {
            'twilio' => $this->sendViaTwilio($phone, $code),
            'nexmo' => $this->sendViaNexmo($phone, $code),
            default => $this->sendViaLocal($phone, $code),
        };
    }

    /**
     * Отправка через Twilio
     */
    private function sendViaTwilio(string $phone, string $code): bool
    {
        try {
            // TODO: Реализовать интеграцию с Twilio
            // $twilio = new Client(config('sms.twilio.account_sid'), config('sms.twilio.auth_token'));
            // $message = sprintf(config('sms.message_template'), $code);
            // 
            // $twilio->messages->create(
            //     $phone,
            //     [
            //         'from' => config('sms.twilio.from'),
            //         'body' => $message
            //     ]
            // );

            Log::info("SMS отправлен через Twilio", [
                'phone' => $phone,
                'code' => $code
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("Ошибка отправки SMS через Twilio", [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправка через Nexmo/Vonage
     */
    private function sendViaNexmo(string $phone, string $code): bool
    {
        try {
            // TODO: Реализовать интеграцию с Nexmo
            Log::info("SMS отправлен через Nexmo", [
                'phone' => $phone,
                'code' => $code
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("Ошибка отправки SMS через Nexmo", [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Локальная отправка (заглушка)
     */
    private function sendViaLocal(string $phone, string $code): bool
    {
        // Для локальной разработки просто логируем
        Log::info("SMS отправлен (локально)", [
            'phone' => $phone,
            'code' => $code,
            'message' => sprintf(config('sms.message_template'), $code)
        ]);

        return true;
    }

    /**
     * Генерировать код подтверждения
     */
    public function generateCode(): string
    {
        return config('sms.development_mode') ? 
            config('sms.development_code') : 
            str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Проверить, истек ли код
     */
    public function isCodeExpired($expiresAt): bool
    {
        return $expiresAt && $expiresAt < now();
    }
}
