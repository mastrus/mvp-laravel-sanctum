<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;



class AuthTest extends TestCase
{

    /**
     * uri api
     */
    const
        API_REGISTER = '/api/auth/register',
        API_LOGIN = '/api/auth/login',
        API_ME = '/api/auth/me',
        API_REFRESH = '/api/auth/refresh',
        API_LOGOUT = '/api/auth/logout';

    /**
     * costanti creazione utente
     */
    const
        CREATE_NAME = 'name',
        CREATE_EMAIL = 'email',
        CREATE_PASSWORD = 'password',
        CREATE_PASSWORD_CONFIRMATION = 'password_confirmation';

    /**
     * costanti utente
     */
    const
        USER_NAME = 'panco2',
        USER_EMAIL = 'panco2@panco.net',
        USER_PASSWORD = '123456f';

    /**
     * testo la creazione degli utenti
     *
     * @return void
     */
    public function testRegister()
    {
        $response = $this->_createUserByApi();
        $email = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_EMAIL];

        //Write the response in laravel.log
        Log::info('test Register ', [$response->getContent()]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            AuthController::REGISTER_KEY => AuthController::REGISTER_MESSAGE
        ]);
        $this->assertArrayHasKey(AuthController::REGISTER_USER_KEY,$response->json());

        $this->_deleteUser($email);
    }

    /**
     * testo la creazione di tue utenti identici
     *
     * @return void
     */
    public function testRegisterSameUser(){

        $response = $this->json('POST', '/api/auth/register', [
            'name'  =>  self::USER_NAME,
            'email'  =>  self::USER_EMAIL,
            'password'  =>  self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD,
        ]);

        //Write the response in laravel.log
        Log::info('test Register same user ', [$response->getContent()]);

        $response = $this->json('POST', '/api/auth/register', [
            'name'  =>  self::USER_NAME,
            'email'  =>  self::USER_EMAIL,
            'password'  =>  self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertEquals('"{\"email\":[\"The email has already been taken.\"]}"', $response->getContent());

        $this->_deleteUser(self::USER_EMAIL);
    }


    /**
     * testo il login
     *
     * @return void
     */
    public function testLogin()
    {
        $response = $this->_createUserByApi();
        $email = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_EMAIL];
        $password = self::USER_PASSWORD;

        // Simulated landing
        $response = $this->_loginByApi($email, $password);

        //Write the response in laravel.log
        Log::info('test Login', [$response->getContent()]);

        // Determine whether the login is successful and receive token
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'message' => 'success'
        ]);

        $this->_deleteUser($email);
    }

    /**
     * verifico i dati dell'utente
     *
     * @return void
     */
    public function testMe(){

        $response = $this->_createUserByApi();
        $email = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_EMAIL];
        $password = self::USER_PASSWORD;
        $name = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_NAME];

        //Write the response in laravel.log
        Log::info('test me', [$response->getContent()]);

        // Simulated landing
        $response = $this->_loginByApi($email, $password);

        //Write the response in laravel.log
        Log::info('test me', [$response->getContent()]);

        $token = $response->json('access_token');

        $response = $this->_getUserData($token);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'email' => $email,
            'name' => $name
        ]);
        $this->assertArrayHasKey('id', $response->json());

        $this->_deleteUser($email);
    }

    /**
     * testo il refresh del token e il richiamo dei dati
     *
     * @return void
     */
    public function testRefresh(){
        $response = $this->_createUserByApi();
        $email = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_EMAIL];
        $password = self::USER_PASSWORD;
        $name = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_NAME];

        //Write the response in laravel.log
        Log::info('test refrest user data', [$response->getContent()]);


        // Simulated landing
        $response = $this->_loginByApi($email, $password);

        //Write the response in laravel.log
        Log::info('test refrest old token', [$response->getContent()]);

        $token = $response->json('access_token');

        $response = $this->_refreshApiToken($token);

        //Write the response in laravel.log
        Log::info('test refrest new token', [$response->getContent()]);

        $newToken = $response->json('access_token');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'token_type' => 'bearer'
        ]);
        $this->assertFalse($token == $newToken);

        $response = $this->_getUserData($newToken);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'email' => $email,
            'name' => $name
        ]);
        $this->assertArrayHasKey('id', $response->json());

        $this->_deleteUser($email);
    }

    /**
     * testo il logout
     *
     * @return void
     */
    public function testLogout(){
        $response = $this->_createUserByApi();
        $email = $response->json(AuthController::REGISTER_USER_KEY)[self::CREATE_EMAIL];
        $password = self::USER_PASSWORD;

        // Simulated landing
        $response = $this->_loginByApi($email, $password);

        $token = $response->json('access_token');

        $response = $this->_logoutUser($token);

        //Write the response in laravel.log
        Log::info('test logout', [$response->getContent()]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            AuthController::LOGOUT_KEY => AuthController::LOGOUT_MESSAGE
        ]);

        // Delete users
        User::where('email',$email)->delete();
    }

    /**
     * testo la chiamata con un token non valido
     *
     * @return void
     */
    public function testErrorToken(){

        $token = rand();

        //mi collego per recuperare i dati utente
        $response = $this->_getUserData($token);

        //Write the response in laravel.log
        Log::info('test testErrorToken', [$response->getContent()]);;

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    }

    /**
     * metodo per la creazione utente
     *
     * @return \Illuminate\Testing\TestResponse
     */
    protected function _createUserByApi(): \Illuminate\Testing\TestResponse
    {
        return $this->json('POST', self::API_REGISTER, [
            self::CREATE_NAME  =>  self::USER_NAME,
            self::CREATE_EMAIL  =>  rand().time().self::USER_EMAIL,
            self::CREATE_PASSWORD  =>  self::USER_PASSWORD,
            self::CREATE_PASSWORD_CONFIRMATION => self::USER_PASSWORD,
        ]);
    }

    /**
     * elimino l'utente
     *
     * @param string $email
     * @return void
     */
    protected function _deleteUser(string $email){
        $user = User::where(self::CREATE_EMAIL,$email);
        $user->delete();
    }

    /**
     * simulo il login con le api
     *
     * @param string $email
     * @param string $password
     * @return \Illuminate\Testing\TestResponse
     */
    protected function _loginByApi(string $email, string $password): \Illuminate\Testing\TestResponse
    {
        return $this->json('POST',self::API_LOGIN,[
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * Ritorno i dati dell'utente tramite il token
     *
     * @param string $token
     * @return \Illuminate\Testing\TestResponse
     */
    protected function _getUserData(string $token): \Illuminate\Testing\TestResponse
    {
        return $this->json('GET',self::API_ME);
    }

    /**
     * Eseguo il logout utente
     *
     * @param string $token
     * @return \Illuminate\Testing\TestResponse
     */
    protected function _logoutUser(string $token): \Illuminate\Testing\TestResponse
    {
        return $this->json('POST',self::API_LOGOUT,[
            'Authorization' => 'Bearer ' . $token
        ]);
    }
}
