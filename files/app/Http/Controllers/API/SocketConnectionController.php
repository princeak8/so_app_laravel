<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Str;

use App\Http\Requests\GetTokenRequest;

use App\Models\SocketConnection;

class SocketConnectionController extends Controller
{
    public function getToken(GetTokenRequest $request)
    {
        $post = $request->all();
        try{
            $connection = SocketConnection::where('name', $post['name'])->first();
            if($connection) {
                if (Hash::check($post['password'], $connection->password)) {
                    do{
                        $random = Str::random(8);
                        $token = hash('md5', $random);
                        $exists = $this->getConnectionByIdToken($connection->id, $token);
                    } while ($exists);
                    $connection->token = $token;
                    $connection->update();
                    return response()->json([
                        'statusCode' => 200,
                        'token' => $token
                    ], 200);
                }else{
                    return response()->json([
                        'statusCode' => 422,
                        'message' => 'name or password is not correct'
                    ], 422);
                }
            }else{
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'This credentials is incorrect'
                ], 404);
            }
        }catch(\Exception $e){
            \Log::stack(['project'])->info($e->getMessage().' in '.$e->getFile().' at Line '.$e->getLine());
            return response()->json([
                'statusCode' => 500,
                'message' => 'An error occured while trying to perform this operation, Please try again later or contact support'
            ], 500);
        }
    }

    private function getConnectionByIdToken($id, $token)
    {
        return SocketConnection::where('id', $id)->where('token', $token)->first();
    }
}


