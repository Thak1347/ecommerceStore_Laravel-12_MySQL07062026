<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;



class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 422);

        }

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message'=>'Invalid credentials'], 401);

        }

        if(!$user->active){
            return response()->json([['message'=>'Account is deactivated']], 403);

        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'=> 'Login successful',
            'user'=> $user,
            'token'=> $token,
            'role'=>$user->role,
        ], 200);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message'=> 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());

    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address'=>'nullable|string',
            'email'=> ['required', 'email', Rule::unique('users')->ignore($user->id)],

        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=> $validator->errors()], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json(['message'=>'Profile updated successfully','user'=> $user
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'current_password' => 'required|string',
            'new_password'=> 'required|string|min:6|confirmed',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=> $validator->errors()], 422);
        }

        $user = $request->user();
        
        if(!Hash::check($request->current_password, $user->password)){
            return response()->json(['message'=> 'Current password is incorrect'],422);

        }
        
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message'=> 'Password changed successfully']);
    }

    /**
     * Get customers list with search and pagination
     */
    public function getCustomers(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $query = User::where('role', 'customer');
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);
        
        // Add orders count and total spent for each customer
        $customers->getCollection()->transform(function($customer) {
            $customer->orders_count = $customer->orders()->count();
            $customer->total_spent = $customer->orders()
                ->where('payment_status', 'paid')
                ->sum('total');
            return $customer;
        });
        
        return response()->json($customers);
    }

        /**
     * Get single customer details
     */
    public function getCustomer($id)
    {
        $user = request()->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        // Add additional data
        $customer->orders_count = $customer->orders()->count();
        $customer->total_spent = $customer->orders()
            ->where('payment_status', 'paid')
            ->sum('total');
        $customer->recent_orders = $customer->orders()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json($customer);
    }

    /**
 * Update customer information
 */
    public function updateCustomer(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'active' => $request->active ?? $customer->active,
        ]);
        
        return response()->json([
            'message' => 'Customer updated successfully',
            'user' => $customer
        ]);
    }

   /**
     * Update customer status (active/inactive)
     */
    public function updateCustomerStatus(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $customer = User::where('role', 'customer')->findOrFail($id);
        $customer->active = $request->active;
        $customer->save();
        
        return response()->json([
            'message' => 'Customer status updated successfully',
            'user' => $customer
        ]);
    }

    /**
     * Delete customer
     */
    public function deleteCustomer($id)
    {
        $user = request()->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        // Check if customer has orders
        if ($customer->orders()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete customer with existing orders'
            ], 422);
        }
        
        $customer->delete();
        
        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }


}
