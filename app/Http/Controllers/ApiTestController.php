<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTestController extends Controller
{
    /**
     * messaggi di risposta validi anche per i test
     */
    const
        INDEX_NAME = 'message',
        OPEN_MESSAGE = "Questa route e' accessibile da chiunque",
        CLOSED_MESSAGE = "Solo le persone autorizzate possono vedere questo";

    /**
     * api di test di libero accesso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeApi(): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                self::INDEX_NAME => self::OPEN_MESSAGE
            ],
            Response::HTTP_OK
        );

    }

    /**
     * Api di test con accesso solo ad utenti registrati
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function onlyMiddelware(): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                self::INDEX_NAME => self::CLOSED_MESSAGE
            ],
            Response::HTTP_OK
        );
    }
}
