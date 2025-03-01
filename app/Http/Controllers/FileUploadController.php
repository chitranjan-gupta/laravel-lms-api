<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\Drive;
use App\Models\GoogleDetail;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Auth;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $user = Auth::user();
        if($user){
           $request->validate([
               'file' => 'required|file|max:2048', // Max file size: 2MB
           ]);

           $file = $request->file('file');
           $filename = $file->getClientOriginalName();
           $name = $file->hashName(); // Generate a unique, random name...
           $extension = $file->extension(); // Determine the file's extension based on the file's MIME type...
           $mimeType = $file->getMimeType();
           Configuration::instance(env('CLOUDINARY_URL'));
           $upload = new UploadApi();
           $fileUploaded = $upload->upload($file->getPathname());
           return response()->json(['message' => 'File uploaded successfully', 'filename' => $filename, 'name' => $name, 'extension' => $extension, 'url' => $fileUploaded["secure_url"]], 200);
          // $google_user = GoogleDetail::first();
          // GoogleDetail::truncate();
          //  if($google_user){
          //      $content = file_get_contents($file->getPathname());
          //      $client = new Client();
          //      $client->setAccessToken($google_user->access_token);
          //      if($client->isAccessTokenExpired()){
          //          return response("Yes token expired", 200);
          //      }else{
          //          return response("No token is not expired", 200);
          //      }
          //      $client->addScope(Drive::DRIVE);
          //      $driveService = new Drive($client);
          //      $fileMetadata = new Drive\DriveFile(array(
          //      'name' => $filename));
          //      $uploadedFile = $driveService->files->create($fileMetadata, array(
          //      'data' => $content,
          //      'mimeType' => $mimeType,
          //      'uploadType' => 'multipart',
          //      'fields' => 'id, webViewLink'));
          //      return response()->json(['message' => 'File uploaded successfully', 'filename' => $filename, 'name' => $name, 'extension' => $extension, 'file_id' => $uploadedFile->id,'url' => $uploadedFile->webViewLink], 200);
          //  }
          //   // Move the uploaded file to the public directory
          //   $path = $file->storeAs('uploads', $name);
          //   // Construct the file URL
          //   $url = env('APP_URL') . '/api/storage/' . $path;
          //   // Return a response
          //   return response()->json(['message' => 'File uploaded successfully', 'filename' => $filename, 'name' => $name, 'extension' => $extension, 'url' => $url], 200);
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function download($any)
    {
        if($any){
            if (Storage::disk('public')->exists($any)) {
                $file = Storage::disk('public')->get($any);
                $mimeType = Storage::disk('public')->mimeType($any);
            
                return response($file, 200)
                    ->header('Content-Type', $mimeType);
                // return Storage::download($any);
            }else{
                return response('File does not exists', 404);
            }
        }else{
            return response('Url is not valid', 400);
        }
    }

    public function delete($any){
        if($any){
            if (Storage::disk('public')->exists($any)) {
                $res = Storage::disk('public')->delete($any);
                return response()->json(['status' => 'Deleted successfully', 'data' => $res], 200);
            }else{
                return response('File does not exists', 404);
            }
        }else{
            return response('Url is not valid', 400);
        }
    }
}
