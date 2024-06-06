## Step 1
```bash
composer global require laravel/installer
laravel new example-app
```
## step 2
```bash
cd example-app
 
php artisan serve
```

## DB configuration 

```bash
php artisan migrate
```
## make controller for API

```bash
php artisan make:controller Api\UserController --resource
```
## change the fillable from user.php

## create a folder in http as Helper in this create a helper.php for output
```bash
composer dump-autoload
```

## create api 
![image](https://github.com/mrpankajpandey/Laravel-API/assets/107976020/728011f1-52be-41df-9a4a-6bf4ca5b1492)

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('user/store','App\Http\Controllers\Api\UserController@store');
Route::get('user/get/{flag}',[UserController::class,'index']);
Route::get('user/{id}',[UserController::class , 'show']);
Route::delete('user/delete/{id}',[UserController::class,'destroy']);
Route::put('user/update/{id}',[UserController::class,'update']);
Route::patch('user/change-password/{id}',[UserController::class,'changePassword']);
```

## http folder API/UserController.php

```php
<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($flag)
    {
        //flag == 1 active users
        // flag == 0 all uesr's
        // p("get Api is working");
        // $users = User::select('name', 'email')->where('status',1)->get();
        
        $query = User::select('name','email');
        if($flag == 1){
            $query->where('status',1);
        }
        else if($flag ==0){
            // $query->where('status',0);
        }
        else{
            return  response()->json([
            'message'=>'Invalid params , It can be either 1 or 0',
            'status'=>0
           ],400);
        }
        $users = $query->get();
       if(count($users) >0){
        $response = [
            'message' =>count($users) .'user Found',
            'status' =>1,
            'data'=>$users
        ];
        return response()->json($response,200);
       }else {
        
           $response =[
               'message' => count($users). ' users not found',
               'status' =>0,
           
           ];
    }
    return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validator =  Validator::make($request->all(), [
            'name'=>['required'],
            'email'=>['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' =>['required'] 
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(),400);
        }else {
            $data = [
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password)
            ];
            p($data);
            DB::beginTransaction();
            try {
                //code...
                $user = User::create($data);
                DB::commit();
            } catch (\Exception $e) {
                //throw $th;
                DB::rollBack();
                p($e->getMessage());
                $user = null;
            }
            if ($user != null) {
                return response()->json([
                    'message' =>'User Created Successfully'
                ],200);
            }else {
                return response()->json([
                    'message' =>'Internal server error'
                ],500);
            }
        }
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user = User::find($id);
        if (is_null($user)) {
            # code...
            $response = [
                'message' =>'User Not Found ',
                'status' =>0
            ];
        }else{
            $response = [
                'message' =>'User Found Successfully ',
                'status' =>1,
                'data' => $user
            ];
        }
        return response()->json($response,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = User::find($id);
        if(is_null($user)){
            $response = [
                'message' =>"User dosn't exits",
                'status' =>0
            ];
            $respCode = 404;
        }else{
            DB::beginTransaction();
            try {
                //code...
                $user->name = $request['name'];
                $user->email = $request['email'];
                $user->save();
                DB::commit();
                
                
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                $user =null;  
            }
            if(is_null($user)){
                $response = [
                    'message' =>"Internal server error",
                    'status' =>0,
                    'Error_msg' =>$th->getMessage()
                ];
                $respCode = 500 ;
            }else{
                $response = [
                    'message' =>"Data updated Sucessfully",
                    'status' =>1
                ];
                $respCode = 200;
            }
        }
        return response()->json($response,$respCode);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $user = User::find($id);
        if(is_null($user)){
            $response = [
                'message' => "User doesn't exits",
                'status' =>0
                
            ];
            $respCode = 404;
        }else{
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    ',message' =>"User Deleted successfully",
                    'status' =>1
                ];
                //code...
                $respCode = 200;
                
            } catch (\Exception $e) {
                //throw $th;
                DB::rollBack();
                $response = [
                    ',message' =>"Interal Server Error",
                    'status' =>0
                ];
                //code...
                $respCode = 500;
                
            }
        }
        return response()->json($response,$respCode);
    }
    public function changePassword(Request $request,$id){
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => "user not found",
                'status' =>0
            ];
            $respCode = 404;
            # code...
        }else {
            if(Hash::check($request['old_password'], $user->password)){
                if($request['new_password'] == $request['confirm_password']){
                    DB::beginTransaction();
                    try {
                        //code...
                        $user->password =  Hash::make($request['new_password']);
                        $user->save();
                        DB::commit();
                        
                        
                    } catch (\Throwable $th) {
                        //throw $th;
                        $user->null;
                        DB::rollBack();
                       
                    }
                    if(is_null($user)){
                        $response = [
                            'message' =>"Internal server error",
                            'status' =>0,
                            'error_msg' =>$th->getMessage()
                        ];
                        $respCode = 500;
                        
                    }else{
                        $response = [
                            'message' =>"Password Changed Sucessfully",
                            'status' =>1
                        ];
                        $respCode = 200;
                    }
                    
                }else{
                    $response = [
                        'message' =>"New Passaword and Confirm password Not matched",
                        'status' =>0
                    ];
                    $respCode = 404;
                }
                
            }else{
                $response = [
                    'message' =>"old Passaword Not matched",
                    'status' =>0
                ];
                $respCode = 404;
            }
        }
        return response()->json($response,$respCode);
    
    }
    
}
```
## Passport Athentication 
```bash
php artisan passport:insatll
```
[link](https://laravel.com/docs/11.x/passport)
## Register
![image](https://github.com/mrpankajpandey/Laravel-API/assets/107976020/32335569-8a95-46db-a943-82d79643ddbe)
## login
![image](https://github.com/mrpankajpandey/Laravel-API/assets/107976020/f0c56ee7-a3c6-461c-8675-17d65af2177a)
## Athentication in API routes 
![image](https://github.com/mrpankajpandey/Laravel-API/assets/107976020/a55ad6ce-4193-4419-a2e6-8b0523d4c9fd)
## Athorizationn
![image](https://github.com/mrpankajpandey/Laravel-API/assets/107976020/4c5e750a-0244-4b2a-afba-8f985a4ba84e)


