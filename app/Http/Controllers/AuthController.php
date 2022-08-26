<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwtauth', ['except' => ['login', 'register']]);
    }

    public function register(Request $request): JsonResponse
    {
        try{
            $user = $request->except( 'password');
            $user['password'] = Hash::make($request->get('password'));
            Users::query()->create($user);
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

        $user = Users::query()
            ->select('id',
                'nickname',
                'avatar',
                'role',
                'password',
                'refresh_token',
            )
            ->where('phone', '=', $request->get('phone'))
            ->first();
        if(!$user || !Hash::check($request->get('password'), $user->password)){
            return response()->json([
                'message' => 'Invalid phone or password'
            ], Response::HTTP_NOT_FOUND);
        }

        // set new access token
        if (!$token = auth()->setTTL(1)->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // set new refresh token
        $refresh_token = auth()->claims(['sid' => $user->id])->setTTL(2)->fromUser($user);
        $user->refresh_token = $refresh_token;
        $user->save();

        $cookie = cookie('jwt', $refresh_token, 2);

        return  response()->json([
            'message' => 'Login in successfully',
            'role' => $user->role,
            'access_token' => $token,
        ], status: 200)->withCookie($cookie);

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
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        auth()->setTTL(1)->refresh();
        $cookie = cookie('jwt', null, -1);
        Users::query()
            ->where('id', $request->get('id'))
            ->update(['refresh_token' => null]);

        return response()->json([
            'message' => 'Successfully logged out'
        ])->withCookie($cookie);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->setTTL(1)->refresh());
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
