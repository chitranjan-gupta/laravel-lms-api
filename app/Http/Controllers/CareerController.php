<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerController extends Controller
{

    public function all(Request $request, $companyId){
        $perPage = $request->input('per_page', 10);
        if($companyId){
            $careers = Career::with(['company'])->where('companyId', $companyId)->orderBy('created_at', 'desc')->paginate($perPage);
            return response()->json($careers, 200);
        }else{
            return response("Company ID is missing", 400);
        }
    }

    public function careers(Request $request){
        $perPage = $request->input('per_page', 10);
        $careers = Career::with(['company'])->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($careers, 200);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user && ($user->role == "subadmin" || $user->role == "admin")) {
            if ($request->has("title") && $request->has("companyId")) {
                $title = $request->input("title");
                $companyId = $request->input("companyId");
                $company = Company::where('id', $companyId)->where('userId', $user->id)->first();
                if (!$company) {
                    return response($user->role == "admin" ? "Not Found" : "Unauthorized", 401);
                }
                $career = Career::create(['title' => $title, 'companyId' => $companyId]);
                return response()->json($career, 200);
            } else {
                return response("Title or Company ID is missing", 400);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function get($companyId, $careerId)
    {
        if ($companyId && $careerId) {
            $career = Career::with(['company'])->where('id', $careerId)->where('companyId', $companyId)->first();
            return response()->json($career, 200);
        }
        return response("Not Found", 404);
    }

    public function find($careerId)
    {
        if ($careerId) {
            $career = Career::with(['company'])->where('id', $careerId)->first();
            return response()->json($career, 200);
        }
        return response("Not Found", 404);
    }

    public function set(Request $request, $companyId, $careerId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($careerId && $companyId) {
                $company = Company::where('id', $companyId)->where('userId', $user->id)->first();
                if (!$company) {
                    return response("Unauthorized", 401);
                }
                $career = Career::where('id', $careerId)->first();
                if (!$career) {
                    return response("Unauthorized", 401);
                }
                $career->update($request->all());
                return response()->json($career, 200);
            } else {
                return response("Not Found", 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($careerId && $companyId) {
                $company = Company::where('id', $companyId)->first();
                if (!$company) {
                    return response("Not Found", 404);
                }
                $career = Career::where('id', $careerId)->first();
                if (!$career) {
                    return response("Not Found", 404);
                }
                $career->update($request->all());
                return response()->json($career, 200);
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function delete($companyId, $careerId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($companyId && $careerId) {
                $company = Career::where('id', $companyId)->where('userId', $user->id)->first();
                if (!$company) {
                    return response("Unauthorized", 401);
                }
                $career = Career::where('id', $careerId)->where('companyId', $companyId)->first();
                if (!$career) {
                    return response("Unauthorized", 401);
                }
                $career->delete();
                return response()->json($career, 200);
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($companyId && $careerId) {
                $company = Company::where('id', $companyId)->first();
                if (!$company) {
                    return response("Not Found", 404);
                }
                $career = Career::where('id', $careerId)->first();
                if (!$career) {
                    return response("Not Found", 404);
                }
                $career->delete();
                return response()->json($career, 200);
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }
}
