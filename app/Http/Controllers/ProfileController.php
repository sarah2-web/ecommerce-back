<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;

class ProfileController extends Controller
{
    // عرض بيانات البروفايل
//     public function index()
// {
//     try {
//         $user = Auth::user();
//         if (!$user) {
//             return response()->json(['message' => 'User not authenticated'], 401);
//         }

//         // ===== جلب آخر نسخة من الـ profile مباشرة =====
//         $profile = Profile::where('user_id', $user->id)->first();

//         $profileData = $profile ? $profile->toArray() : [];
//         $profileData['avatar_url'] = !empty($profileData['avatar'])
//             ? asset('storage/avatars/' . $profileData['avatar'])
//             : asset('storage/avatars/no-avatar.jpg');

//         return response()->json([
//             'user_id' => $user->id,
//             'email' => $user->email,
//             'profile' => $profileData
//         ]);

//     } catch (\Exception $e) {
//         Log::error('Profile index error: ' . $e->getMessage());
//         Log::error($e->getTraceAsString());

//         return response()->json([
//             'message' => 'Server error: ' . $e->getMessage(),
//             'error' => $e->getMessage(),
//             'file' => $e->getFile(),
//             'line' => $e->getLine()
//         ], 500);
//     }
// }
public function index()
{
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $profile = $user->profile;

        return response()->json([
            'profile_raw' => $profile, // مؤقت للتجربة
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
    // public function index()
    // {
    //     try {
    //         $user = Auth::user();
    //         if (!$user) return response()->json(['message' => 'User not authenticated'], 401);

    //         $profile = $user->profile;
    //         $profileData = $profile ? $profile->toArray() : [];
    //         $profileData['avatar_url'] = !empty($profileData['avatar'])
    //             ? asset('storage/avatars/' . $profileData['avatar'])
    //             : asset('storage/avatars/no-avatar.jpg');

    //         return response()->json([
    //             'user_id' => $user->id,
    //             'email' => $user->email,
    //             'profile' => $profileData
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Profile index error: ' . $e->getMessage());
    //         return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
    //     }
    // }

    // إنشاء أو تعديل البروفايل

    public function storeOrUpdate(UpdateUserRequest $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            // ---------------- User ----------------
            $userData = $request->only(['name', 'email', 'password', 'phone', 'address']);

            if (isset($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            }

            // تحديث المستخدم
            User::where('id', $userId)->update($userData);
            $user = User::find($userId);

            // ---------------- Profile ----------------
            $profile = Profile::firstOrNew(['user_id' => $userId]);

            $profileData = $request->only([
                'name',
                'phone',
                'address',
                'birthdate'
            ]);

            // معالجة الصورة
            if ($request->hasFile('avatar')) {
                if ($profile->avatar) {
                    Storage::disk('public')->delete('avatars/' . $profile->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $profileData['avatar'] = basename($path);
            }

            // تحديث أو إنشاء البروفايل
            if ($profile->exists) {
                $profile->update($profileData);
            } else {
                $profileData['user_id'] = $userId;
                $profile = Profile::create($profileData);
            }

            // ---------------- Response ----------------
            return response()->json([
                'message' => 'Profile saved successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'profile' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                    'birthdate' => $profile->birthdate,
                    'avatar_url' => $profile->avatar
                        ? asset('storage/avatars/' . $profile->avatar)
                        : asset('storage/avatars/no-avatar.jpg'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profile store/update error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
