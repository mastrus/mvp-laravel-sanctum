<?php

namespace Tests\Feature;


use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Http\Controllers\ApiTestController;

class ApiTest extends TestCase
{
    /**
     * testa l'end point di test /open esterno al middelware
     *
     * @return void
     */
    public function testOpenApi(){


        $response = $this->json('GET', '/api/test/open');

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                ApiTestController::INDEX_NAME => ApiTestController::OPEN_MESSAGE
            ]);
    }

    /**
     * testa l'end point di test /closed interno al middelware
     *
     * @return void
     */
    public function testClosedApi(){
        $response = $this->json('GET', '/api/test/closed');

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                ApiTestController::INDEX_NAME => ApiTestController::CLOSED_MESSAGE
            ]);;
    }
}
