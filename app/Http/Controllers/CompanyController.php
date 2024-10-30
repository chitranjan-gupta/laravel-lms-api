<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{

    public function all(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $companies = Company::with(['careers'])->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($companies, 200);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user && ($user->role == "subadmin" || $user->role == "admin")) {
            if ($request->has("name")) {
                $name = $request->input("name");
                $company = Company::create(['name' => $name, 'userId' => $user->id]);
                return response()->json($company, 200);
            } else {
                return response("Name is missing", 400);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function get($companyId)
    {
        if ($companyId) {
            $company = Company::with(['careers'])->find($companyId);
            return response()->json($company, 200);
        }
        return response("Not Found", 404);
    }

    public function set(Request $request, $companyId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($companyId) {
                $company = Company::where('id', $companyId)->where('userId', $user->id)->first();
                if (!$company) {
                    return response("Unauthorized", 401);
                }
                $company->update($request->all());
                return response()->json($company, 200);
            } else {
                return response("Not Found", 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($companyId) {
                $company = Company::where('id', $companyId)->first();
                if (!$company) {
                    return response("Not Found", 404);
                }
                $company->update($request->all());
                return response()->json($company, 200);
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function delete($companyId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($companyId) {
                $company = Company::where('id', $companyId)->where('userId', $user->id)->first();
                if (!$company) {
                    return response("Unauthorized", 401);
                }
                $company->delete();
                return response()->json($company, 200);
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($companyId) {
                $company = Company::where('id', $companyId)->first();
                if (!$company) {
                    return response("Unauthorized", 401);
                }
                $company->delete();
                return response()->json($company, 200);
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }
}
