<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credenciais = $request->all(['email', 'password']);

        // autenticação (email e senha)
        $token = auth('api')->attempt($credenciais);
        
        if($token){
            return response()->json(['token' => $token]);
        }else{
            return response()->json(['erro' => 'Usuário ou senha inválido'], 403);

            // 401 = unauthorized -> não autorizado
            // 403 = forbidden -> proibido (login inválido)
        }
    }

    public function logout()
    {
        return 'logout';
    }

    public function refresh()
    {
        return 'refresh';
    }

    public function me()
    {
        // dd(auth()->user()); //aqui o password é apresentado
        return response()->json(auth()->user()); //aqui o password NÃO é apresentado
    }
}
