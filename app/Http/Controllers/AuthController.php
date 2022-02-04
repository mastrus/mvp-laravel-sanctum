<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;


class AuthController extends Controller
{
    /**
     * lunghezza minima e massima dei parametri
     */
    const
        EMAIL_MIN_MAX_LENGHT = 'min:4|max:100',
        PASSWORD_MIN_MAX_LENGHT = 'min:6|max:25';

    /**
     * risposte del metodo register
     */
    const
        REGISTER_KEY = 'message',
        REGISTER_MESSAGE = 'User successfully registered',
        REGISTER_USER_KEY = 'user';

    /**
     * risposta del metodo logout
     */
    const
        LOGOUT_KEY = 'message',
        LOGOUT_MESSAGE = 'Successfully logged out';


    /**
     * Register a User.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|' . self::EMAIL_MIN_MAX_LENGHT . '|unique:users',
            'password' => 'required|string|confirmed|' . self::PASSWORD_MIN_MAX_LENGHT,
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' =>  Hash::make($request->password)]
                ));

        return response()->json([
            self::REGISTER_KEY => self::REGISTER_MESSAGE,
            self::REGISTER_USER_KEY => $user
        ], Response::HTTP_CREATED);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {

        $credentials = $request->only('email', 'password');

        if ($this->guard()->attempt($credentials)) {

            /** @var \App\Models\User */
            $user = $this->guard()->user();

            /** @var string $token */
            $token = $user->createToken('token')->plainTextToken;

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        $cookie = Cookie::forget('jwt');

        return response()
            ->json([self::LOGOUT_KEY => self::LOGOUT_MESSAGE])
            ->withCookie($cookie);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken(string $token): \Illuminate\Http\JsonResponse
    {
        $cookie = cookie('jwt', $token, 60 * 24); // 1 day

        return response()->json([
            'message' => 'success',
        ])->withCookie($cookie);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    protected function guard(): \Illuminate\Contracts\Auth\Guard
    {
        return Auth::guard();
    }
}
