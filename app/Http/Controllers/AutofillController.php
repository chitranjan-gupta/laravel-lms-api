<?php

namespace App\Http\Controllers;

use App\Models\Autofill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AutofillController extends Controller
{
    public function get(Request $request){
        $user = Auth::user();
        if($user){
            $autofill = Autofill::where('userId', $user->id)->first();
            if(!$autofill){
                return response()->json(['data' => json_encode([])], 200);
            }
            return response()->json($autofill, 200);
        }else{
            return response("Unauthorized", 401);
        }
    }

    public function set(Request $request){
        $user = Auth::user();
        if($user && $request->has('data')){
            $data = $request->input('data');
            $autofill = Autofill::where('userId', $user->id)->first();
            if(!$autofill){
                $autofill = Autofill::create(['userId' => $user->id, 'data' => json_encode($data)]);
            }
            $autofill->update(['data' => json_encode($data)]);
            return response()->json($autofill, 200);
        }else{
            return response("Unauthorized", 401);
        }
    }

    public function getAll(){
        $autofills = Autofill::all();
        return response()->json($autofills, 200);
    }
}
