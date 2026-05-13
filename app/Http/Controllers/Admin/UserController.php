<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the users
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\View\View
     */
    public function index(User $model)
    {
        return view('pages.users.index', ['users' => $model->all()]);
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->back()->with('sucesso','Usuário apagado com sucesso!');
    }

    public function storeAdmin(Request $request)
    {
        /**
         * Validador de informações
         */
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        /**
         * Cadastra usuário
         */

        User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
        return redirect()->back()->with('sucesso','Usuário cadastrado com sucesso!');
    }


    public function storeApp(Request $request)
    {
        /**
         * Validador de informações
         */
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        /**
         * Cadastra usuário
         */

        User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'type' => 1,
            'app_type' => (isset($request['app_type'])?$request['app_type']:1)
        ]);
        
        return redirect()->back()->with('sucesso','Usuário cadastrado com sucesso!');
    }

    public function update(Request $request)
    {
        if($request->password == null){
            $user = User::find($request->id);
            if (isset($request['app_type'])) {
                $user->update([
                    "name" => $request["name"],
                    "email" => $request["email"],
                    'app_type' => (isset($request['app_type'])?$request['app_type']:1)
                ]);
            }else{
                $user->update([
                    "name" => $request["name"],
                    "email" => $request["email"]
                ]);
            }
            $user->save();
            return redirect()->back()->with('sucesso','Usuário atualizado com sucesso!');
        }else{
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('falha', 'Erro ao atualizar usuário!')->withInput();
            }
            $user = User::find($request->id);
            if (isset($request['app_type'])) {
                $user->update([
                    "name" => $request["name"],
                    "email" => $request["email"],
                    'app_type' => (isset($request['app_type'])?$request['app_type']:1),
                    "password" => Hash::make($request['password'])
                ]);
            }else{
                $user->update([
                    "name" => $request["name"],
                    "email" => $request["email"],
                    "password" => Hash::make($request['password'])
                ]);
            }
            $user->save();
            return redirect()->back()->with('sucesso','Usuário atualizado com sucesso!');
        }
    }
}
