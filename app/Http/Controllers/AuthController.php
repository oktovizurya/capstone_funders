<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Redirect,Response,File, Storage;

use App\Models\User;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Kabkota;
use App\Models\Status;
use App\Models\Dataset;
use App\Models\Provinsi;
use App\Models\Kategori;
use App\Models\RangeEmployees;
use App\Models\RangeFunds;
use App\Models\Recommendations;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'get_role', 'get_all_pengusaha', 'get_all_investor', 'refresh_token', 
        'get_provinsi_kabkota', 'get_all_provinsi', 'get_status', 'get_kategori', 'get_range_fund', 'get_range_employee', 'get_all_user']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(){

        $credentials = request(['email', 'password']);


        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['status' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|confirmed|min:6',
                // 'gambar' => 'mimes:jpg,jpeg,png|max:5048',
                // 'no_telp' => 'required',
                'id_role' => 'required',
                'id_status' => 'required',
            ]);
    
            $email = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:users'
            ]);
    
            $password = Validator::make($request->all(), [
                'password' => 'required|string|min:6|confirmed'
            ]);
    
            $gambar = Validator::make($request->all(), [
                'gambar' => 'mimes:jpg,jpeg,png|max:5048'
            ]);
    
            if($email->fails()){
                 return response()->json(['message' => 'Email sudah terdaftar'], 401);
            }
    
            if($password->fails()){
                return response()->json(['message' => 'Password minimal 6 karakter dan harus sama dengan confirmation password'], 401);
            }
    
            if($gambar->fails()){
                return response()->json(['message' => 'Gambar tidak berekstensi jpg / jpeg / png atau lebih besar dari 5MB'], 401);
            }
    
            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }
    
            if (isset($request->gambar)) {
                $gambar = $request->file('gambar')->store('uploads/profile', 'public');
            } else {
                $gambar = NULL;
            }
    
            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));
    
            $profile = Profile::create([
                'id_user' => $user->id,
                'no_telp' => $request->no_telp,
                'gambar' => basename($gambar),
            ]);
    
            $dataset = Dataset::create([
                'id_user' => $user->id,
            ]);
    
            return response()->json([
                'message' => 'Berhasil terdaftar'
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function logout() {

        auth('api')->logout();

        return response()->json(['status' => 'Token is Destroyed']);
    }

    public function refresh_token() {
        $token = auth('api')->getToken();
        if (!$token) {
            return response()->json(['status' => 'Not able to Refresh Token'], 400);
        }

        try {
            $refreshedToken = auth('api')->refresh($token);
        } catch (JWTException $e) {
            return response()->json(['status' => 'Invalid Token'], 400);
        }

        return $this->respondWithToken($refreshedToken);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'status' => 'ok',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }

    public function user_profile() {

        try {

            $user = User::where('id', auth('api')->user()->id)->first();
            $profile = Profile::where('id_user', auth('api')->user()->id)->first();
            $dataset = Dataset::where('id_user', auth('api')->user()->id)->first();
    
            return response()->json([
                'user' => [
                    'id' => $user->id ?? '-',
                    'name' => $user->name ?? '-',
                    'email' => $user->email ?? '-',
                    'role' => $user->role->role ?? '-',
                    'status' => $user->status->status ?? '-',
                    'no_telp' => $profile->no_telp ?? '-',
                    'gambar' => $profile->gambar ?? '-',
                    'alamat' => $profile->alamat ?? '-',
                    'deskripsi' => $profile->deskripsi ?? '-',
                    'provinsi' => $profile->kabkota->provinsi->provinsi ?? '',
                    'kabkota' => $profile->kabkota->kabkota ?? '-',
                    'dataset' => [
                        'lokasi' => $dataset->lokasi->provinsi ?? '-',
                        'kategori' => $dataset->kategori->kategori ?? '-',
                        'range_fund' => $dataset->range_fund->range_fund ?? '-',
                        'range_employee' => $dataset->range_employee->range_employee ?? '-'
                    ],
                ]
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function update_profile(Request $request)
    {
        try {
            $user = User::where('id', auth('api')->user()->id)->first();
            $profile = Profile::where('id_user', $user->id)->first();

            if (isset($request->gambar)) {
                $gambar = Validator::make($request->all(), [
                    'gambar' => 'mimes:jpg,jpeg,png|max:5048'
                ]);
                if($gambar->fails()){
                    return response()->json(['message' => 'Gambar tidak berekstensi jpg / jpeg / png atau lebih besar dari 5MB'], 401);
                }
                Storage::delete($profile->gambar);
                $upload_gambar = $request->file('gambar')->store('uploads/profile', 'public');
                $gambar = basename($upload_gambar);
            } else {
                $gambar = $profile->gambar;
            }

            $user_update = User::where('id', auth('api')->user()->id)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $profile_update = Profile::where('id', $profile->id)->update([
                'no_telp' => $request->no_telp,
                'deskripsi' => $request->deskripsi,
                'id_kabkota' => $request->id_kabkota,
                'alamat' => $request->alamat,
                'gambar' => $gambar,
            ]);

            $user = User::where('id', auth('api')->user()->id)->first();
            $profile = Profile::where('id_user', $user->id)->first();

            return response()->json([
                'message' => 'Berhasil di update',
            ], 200);

          } catch (\Illuminate\Database\QueryException $e) {
            $email = Validator::make($request->all(), [
                'email' => 'unique:users,email,'.$user->id
            ]);
            if($email->fails()){
                return response()->json(['message' => 'Email sudah terdaftar'], 401);
            }
            return response()->json(['message' => 'Gagal update profile'], 400);
          }
    }

    public function update_dataset(Request $request)
    {
        try {
            $dataset_update = Dataset::where('id', auth('api')->user()->id)->update([
                'id_kategori' => $request->id_kategori,
                'id_lokasi' => $request->id_lokasi,
                'id_range_funds' => $request->id_range_funds,
                'id_range_employees' => $request->id_range_employees,
            ]);
    
            return response()->json([
                'message' => 'Berhasil di update'
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_role() {
        try {
            $data = Role::get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_status() {
        try {
            $data = Status::get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_all_pengusaha() {
        try {
            $data = Profile::select('users.id', 'name', 'email', 'alamat', 'no_telp', 
            'status', 'provinsi.provinsi', 'kabkota',
            'kategori', 'p_ds.provinsi as lokasi', 'range_fund', 'range_employee')
            ->leftJoin('users', 'profile.id_user', 'users.id')
            ->leftJoin('kabkota', 'profile.id_kabkota', 'kabkota.id')
            ->leftJoin('provinsi', 'kabkota.id_provinsi', 'provinsi.id')
            ->leftJoin('status', 'users.id_status', 'status.id')
            ->leftJoin('role', 'users.id_role', 'role.id')
            ->leftJoin('dataset', 'users.id', 'dataset.id_user')
            ->leftJoin('kategori', 'dataset.id_kategori', 'kategori.id')
            ->leftJoin('provinsi as p_ds', 'dataset.id_lokasi', 'p_ds.id')
            ->leftJoin('range_funds', 'dataset.id_range_funds', 'range_funds.id')
            ->leftJoin('range_employees', 'dataset.id_range_employees', 'range_employees.id')
            ->where('users.id_role', 3)
            ->where('users.id_status', 1)
            ->get();
            
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_all_user() {
        try {
            $data = Profile::select('users.id', 'name', 'email', 'alamat', 'no_telp', 
            'status', 'provinsi.provinsi', 'kabkota',
            'kategori', 'p_ds.provinsi as lokasi', 'range_fund', 'range_employee')
            ->leftJoin('users', 'profile.id_user', 'users.id')
            ->leftJoin('kabkota', 'profile.id_kabkota', 'kabkota.id')
            ->leftJoin('provinsi', 'kabkota.id_provinsi', 'provinsi.id')
            ->leftJoin('status', 'users.id_status', 'status.id')
            ->leftJoin('role', 'users.id_role', 'role.id')
            ->leftJoin('dataset', 'users.id', 'dataset.id_user')
            ->leftJoin('kategori', 'dataset.id_kategori', 'kategori.id')
            ->leftJoin('provinsi as p_ds', 'dataset.id_lokasi', 'p_ds.id')
            ->leftJoin('range_funds', 'dataset.id_range_funds', 'range_funds.id')
            ->leftJoin('range_employees', 'dataset.id_range_employees', 'range_employees.id')
            ->get();
            
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_all_investor() {

        try {
            $data = Profile::select('users.id', 'name', 'email', 'alamat', 'no_telp', 
            'status', 'provinsi.provinsi', 'kabkota',
            'kategori', 'p_ds.provinsi as lokasi', 'range_fund', 'range_employee')
            ->leftJoin('users', 'profile.id_user', 'users.id')
            ->leftJoin('kabkota', 'profile.id_kabkota', 'kabkota.id')
            ->leftJoin('provinsi', 'kabkota.id_provinsi', 'provinsi.id')
            ->leftJoin('status', 'users.id_status', 'status.id')
            ->leftJoin('role', 'users.id_role', 'role.id')
            ->leftJoin('dataset', 'users.id', 'dataset.id_user')
            ->leftJoin('kategori', 'dataset.id_kategori', 'kategori.id')
            ->leftJoin('provinsi as p_ds', 'dataset.id_lokasi', 'p_ds.id')
            ->leftJoin('range_funds', 'dataset.id_range_funds', 'range_funds.id')
            ->leftJoin('range_employees', 'dataset.id_range_employees', 'range_employees.id')
            ->where('id_role', 2)
            ->where('id_status', 1)
            ->get();
            
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_all_provinsi() {
        try {
            $data = Provinsi::get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_provinsi_kabkota(Request $request) {
        try {
            $id = $request->id_provinsi;
            $data = Kabkota::where('id_provinsi', $id)->get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_kategori() {
        try {
            $data = Kategori::get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_range_fund() {
        try {
            $data = RangeFunds::get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function get_range_employee() {
        try {
            $data = RangeEmployees::get();
            return response()->json(compact('data'));
        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }

    public function recommendation() {
       try {

        $user = User::where('id', auth('api')->user()->id)->first();
        $list = Recommendations::where('id_user', $user->id)->orderBy('id', 'ASC')->get();
        $data = [];

            foreach ($list as $value) {
                $list = [
                    'name' => $value->rekomendasi->name ?? '-',
                    'email' => $value->rekomendasi->email ?? '-',
                    'role' => $value->rekomendasi->role->role ?? '-',
                    'status' => $value->rekomendasi->status->status ?? '-',
                    'no_telp' => $value->rekomendasi->profile->no_telp ?? '-',
                    'gambar' => $value->rekomendasi->profile->gambar ?? '-',
                    'alamat' => $value->rekomendasi->profile->alamat ?? '-',
                    'deskripsi' => $value->rekomendasi->profile->deskripsi ?? '-',
                    'provinsi' => $value->rekomendasi->profile->kabkota->provinsi->provinsi ?? '',
                    'kabkota' => $$value->rekomendasi->profile->kabkota->kabkota ?? '-',
                    'dataset' => [
                        'lokasi' => $value->rekomendasi->dataset->lokasi->provinsi ?? '-',
                        'kategori' => $value->rekomendasi->dataset->kategori->kategori ?? '-',
                        'range_fund' => $value->rekomendasi->dataset->range_fund->range_fund ?? '-',
                        'range_employee' => $value->rekomendasi->dataset->range_employee->range_employee ?? '-'
                    ],
                ];
                array_push($data, $list);
            }
            
            return response()->json([
                'data' => $data
            ], 200);

        } catch (QueryException $e) {
            return response()->json(['message' => 'Data Error'], 500);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Data Tidak Ditemukan'], 404);
        }
    }
}
