<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Получить все адреса пользователя
     */
    public function index(Request $request)
    {
        try {
            $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'addresses' => $addresses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении адресов'
            ], 500);
        }
    }

    /**
     * Добавить новый адрес
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'street' => 'required|string|max:255',
                'house_number' => 'required|string|max:50',
                'apartment' => 'nullable|string|max:50',
                'entrance' => 'nullable|string|max:10',
                'floor' => 'nullable|string|max:10',
                'intercom' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:100',
                'district' => 'nullable|string|max:100',
                'comment' => 'nullable|string|max:500',
                'type' => 'nullable|string|in:home,work,other',
                'title' => 'nullable|string|max:100',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = $request->user()->id;
            $data['city'] = $data['city'] ?? 'Ташкент';
            $data['type'] = $data['type'] ?? 'home';

            // Если это адрес по умолчанию, сбросим флаг у других адресов
            if (isset($data['is_default']) && $data['is_default']) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            $address = Address::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Адрес успешно добавлен',
                'address' => $address
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении адреса'
            ], 500);
        }
    }

    /**
     * Обновить адрес
     */
    public function update(Request $request, $id)
    {
        try {
            $address = $request->user()->addresses()->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'street' => 'sometimes|required|string|max:255',
                'house_number' => 'sometimes|required|string|max:50',
                'apartment' => 'nullable|string|max:50',
                'entrance' => 'nullable|string|max:10',
                'floor' => 'nullable|string|max:10',
                'intercom' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:100',
                'district' => 'nullable|string|max:100',
                'comment' => 'nullable|string|max:500',
                'type' => 'nullable|string|in:home,work,other',
                'title' => 'nullable|string|max:100',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Если это адрес по умолчанию, сбросим флаг у других адресов
            if (isset($data['is_default']) && $data['is_default']) {
                $request->user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
            }

            $address->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Адрес успешно обновлен',
                'address' => $address->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении адреса'
            ], 500);
        }
    }

    /**
     * Удалить адрес
     */
    public function destroy(Request $request, $id)
    {
        try {
            $address = $request->user()->addresses()->findOrFail($id);
            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Адрес успешно удален'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении адреса'
            ], 500);
        }
    }

    /**
     * Установить адрес по умолчанию
     */
    public function setDefault(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Найти адрес пользователя
            $address = $user->addresses()->findOrFail($id);
            
            // Сбросить флаг у всех адресов пользователя
            $user->addresses()->update(['is_default' => false]);
            
            // Установить выбранный адрес как основной
            $address->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Адрес установлен по умолчанию',
                'address' => $address->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при установке адреса по умолчанию'
            ], 500);
        }
    }
}
