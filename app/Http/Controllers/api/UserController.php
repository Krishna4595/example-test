<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Hobbies;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\Api\ResponseHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Validator;
use JWTFactory;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * Store a User data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $input = $request->toArray();
            if (!empty($input['user_photo'])) {
                $imageName =  ResponseHelper::uploadImage($input['user_photo']);
                $input['user_photo'] = $imageName;
            }
            if(!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }
            $input['status'] = 'Active';
            $user = User::create($input);
            $apiData = [];
            $apiStatus = ($user) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
            $apiMessage = ($user) ? trans('User Created Sucessfully')
                : trans('There is some error.');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (Exception $ex) {
            return $this->internalServerError();
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        try {
            $input = $request->toArray();
            $validator = Validator::make($input, [
                'phone_number' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users,phone_number,NULL,id,deleted_at,NULL',
                'user_photo' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $validator->errors()->first()
                );
            }
            if (!empty($input['user_photo'])) {
                $imageName =  ResponseHelper::uploadImage($input['user_photo']);
                $input['user_photo'] = $imageName;
            }
            $user->update($input);
            $apiData = [];
            $apiStatus = ($user) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
            $apiMessage = ($user) ? trans('User Updated Sucessfully')
                : trans('There is some error.');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (Exception $ex) {
            return $this->internalServerError();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            $apiData = [];
            $apiStatus = ($user) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
            $apiMessage = ($user) ? trans('User Deleted Sucessfully')
                : trans('There is some error.');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (Exception $ex) {
            return $this->internalServerError();
        }
    }

    /**
     * User login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $input = $request->toArray();
            $validator = Validator::make($input, [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $validator->errors()->first()
                );
            }
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return $this->responseHelper->error(
                    Response::HTTP_UNAUTHORIZED,
                    trans('Invalid Credentials')
                );
            }
            $user = auth()->user();
            $user['token'] = $token;
            $apiData = $user->toArray();
            $apiStatus = ($user) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
            $apiMessage = ($user) ? trans('User login successfully')
                : trans('There is some error.');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (Exception $ex) {
            return $this->internalServerError();
        }
    }

    /**
     * User logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $token = request()->bearerToken();

        try {
            JWTAuth::invalidate($token);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('User has been logged out');
            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (JWTException $exception) {
            return $this->responseHelper->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                trans('Sorry, user cannot be logged out')
            );
        }
    }

    /**
     * User Added Hobbies.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addHobbies(Request $request)
    {
        try {
            $user = JWTAuth::user();
            $input = $request->toArray();
            $validator = Validator::make($input, [
                'hobbies' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $validator->errors()->first()
                );
            }
            $hobbies = json_decode($input['hobbies']);
            $user->hobbies()->sync($hobbies,false);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('User hobbies has been updated');
            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (Exception $ex) {
            return $this->internalServerError();
        }
    }

    /**
     * User List.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function usersList(Request $request)
    {
        try {
            $user = JWTAuth::user();
            $input = $request->toArray();
            $validator = Validator::make($input, [
                'hobbies' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $validator->errors()->first()
                );
            }
            $users = [];
            $hobbies = Hobbies::with('users')->where('name', 'like', '%' . $input['hobbies'] . '%')->first();
            if(!empty($hobbies)) {
                if(!empty($hobbies['users'])) {
                    $users = $hobbies['users']->toArray();
                }
            }
            $apiData = $users;
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('Users listing');
            return $this->responseHelper->success($apiStatus, $apiMessage,$apiData);
        } catch (Exception $ex) {
            return $this->internalServerError();
        }
    }

}
