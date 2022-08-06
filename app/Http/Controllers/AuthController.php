<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwtauth', ['except' => ['login']]);
    }

    public function register(Request $request): JsonResponse
    {
        try{
            $user = $request->except( 'password');
            $user['password'] = Hash::make($request->get('password'));
            Users::create($user);
            return response()->json([
                'status' => 200,
                'message' => 'Register successfully'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'error' => $e,
                'message' => 'Register failed'
            ]);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only(['phone', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Users::query()
            ->select('id',
                'nickname',
                'avatar',
                'role',
                'password'
            )
            ->where('phone', '=', $request->get('phone'))
            ->first();
        if(!$user || !Hash::check($request->get('password'), $user->password)){
            return response()->json([
                'message' => 'Invalid phone or password'
            ], Response::HTTP_NOT_FOUND);
        }

        return  response()->json([
            'message' => 'Login in successfully',
            'role' => $user->role,
            'access_token' => $token,
        ], status: 200);

//        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function processData(): JsonResponse
    {
        try{
            $data = Users::query()->get();
            return response()->json([
                'status' => 200,
                'data' => $data,
                'message' => 'Register failed'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'error' => $e,
                'message' => 'Register failed'
            ]);
        }
    }
}
