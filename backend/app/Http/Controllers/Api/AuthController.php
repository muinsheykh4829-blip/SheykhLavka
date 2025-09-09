<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Регистрация пользователя
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|unique:users,email', // Email опционален
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        // Генерируем код подтверждения (в разработке используем фиксированный код)
        $verificationCode = config('sms.development_mode', true) ? 
            config('sms.development_code', '1234') : 
            str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email, // Может быть null
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // В режиме разработки просто логируем SMS
        if (config('sms.development_mode', true)) {
            \Log::info('SMS код (разработка)', [
                'phone' => $user->phone,
                'code' => $verificationCode
            ]);
        }

        // В разработке возвращаем код в ответе для удобства тестирования
        return response()->json([
            'success' => true,
            'message' => 'Пользователь создан. Подтвердите номер телефона.',
            'data' => [
                'user_id' => $user->id,
                'verification_code' => $verificationCode, // Только для разработки!
            ]
        ], 201);
    }

    /**
     * Подтверждение кода
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }

        // Отладочная информация для разработки
        $inputCode = trim($request->code);
        $dbCode = trim($user->verification_code ?? '');

        if (!$dbCode) {
            return response()->json([
                'success' => false,
                'message' => 'Код подтверждения не найден или уже использован'
            ], 400);
        }

        if ($dbCode !== $inputCode) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный код подтверждения'
            ], 400);
        }

        if ($user->verification_code_expires_at && $user->verification_code_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Код подтверждения истек'
            ], 400);
        }

        // Подтверждаем пользователя
        $user->update([
            'phone_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        // Создаем токен
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Номер телефона подтвержден',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Вход в систему
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный номер телефона или пароль'
            ], 401);
        }

        if (!$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Номер телефона не подтвержден'
            ], 401);
        }

        // Обновляем время последнего входа
        $user->update(['last_login_at' => now()]);

        // Создаем токен
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Успешный вход в систему',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Успешный выход из системы'
        ]);
    }

    /**
     * Повторная отправка кода
     */
    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if ($user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Номер телефона уже подтвержден'
            ], 400);
        }

        // Генерируем новый код (в разработке используем фиксированный код)
        $verificationCode = config('sms.development_mode', true) ? 
            config('sms.development_code', '1234') : 
            str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->update([
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // В режиме разработки просто логируем SMS
        if (config('sms.development_mode', true)) {
            \Log::info('SMS код (разработка)', [
                'phone' => $user->phone,
                'code' => $verificationCode
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Код подтверждения отправлен повторно',
            'data' => [
                'verification_code' => $verificationCode, // Только для разработки!
            ]
        ]);
    }

    /**
     * Отправка SMS для входа по номеру телефона
     */
    public function sendLoginSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ищем пользователя или создаем нового
        $user = User::where('phone', $request->phone)->first();
        
        if (!$user) {
            // Создаем нового пользователя с дефолтными данными
            $user = User::create([
                'name' => 'Пользователь',
                'phone' => $request->phone,
                'password' => Hash::make('123456'), // Дефолтный пароль
            ]);
        }

        // Генерируем код подтверждения
        $verificationCode = config('sms.development_mode', true) ? 
            config('sms.development_code', '1234') : 
            str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        // Обновляем код у пользователя
        $user->update([
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // В режиме разработки просто логируем SMS
        if (config('sms.development_mode', true)) {
            \Log::info('SMS код для входа (разработка)', [
                'phone' => $user->phone,
                'code' => $verificationCode
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'SMS код отправлен',
            'data' => [
                'user_id' => $user->id,
                'verification_code' => $verificationCode, // Только для разработки!
            ]
        ]);
    }

    /**
     * Обновление профиля пользователя
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'first_name',
            'last_name',
            'gender',
            'avatar'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Профиль обновлен',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'gender' => $user->gender,
                    'avatar' => $user->avatar,
                ]
            ]
        ]);
    }

    /**
     * Получение профиля пользователя
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'gender' => $user->gender,
                    'avatar' => $user->avatar,
                ]
            ]
        ]);
    }
}
