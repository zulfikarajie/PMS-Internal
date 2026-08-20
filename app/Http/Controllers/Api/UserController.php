<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('roles', 'permissions')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'function_role' => 'nullable|string',
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'function_role' => $request->function_role,
            'system_role' => $request->role,
            'status' => 'active',
        ]);

        $user->assignRole($request->role);

        AuditLog::record('create_user', $user, null, $user->toArray());

        return response()->json([
            'message' => 'User berhasil dibuat',
            'user' => $user->load('roles'),
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'function_role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldValue = $user->only(['name', 'email', 'function_role']);

        $user->update($request->only(['name', 'email', 'function_role']));

        AuditLog::record('update_user', $user, $oldValue, $request->only(['name', 'email', 'function_role']));

        return response()->json([
            'message' => 'User berhasil diupdate',
            'user' => $user->load('roles'),
        ]);
    }

    public function assignRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldRole = $user->system_role;

        $user->syncRoles([$request->role]);
        $user->system_role = $request->role;
        $user->save();

        AuditLog::record('update_role', $user, ['role' => $oldRole], ['role' => $request->role]);

        return response()->json([
            'message' => 'Role berhasil diupdate',
            'user' => $user->load('roles'),
        ]);
    }

    public function toggleStatus(Request $request, User $user)
    {
        $oldStatus = $user->status;
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        AuditLog::record('update_status', $user, ['status' => $oldStatus], ['status' => $user->status]);

        return response()->json([
            'message' => 'Status user berhasil diubah',
            'user' => $user,
        ]);
    }

    public function destroy(User $user)
    {
        AuditLog::record('delete_user', $user, $user->toArray(), null);

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}