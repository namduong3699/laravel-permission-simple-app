<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Auth;

//Import Spatie Laravel Premission models
use App\Role;
use App\Premission;

//Use session to display flash message
use Session;

class UserController extends Controller
{
    public function __construct() {
        //Only Admin has special permission to access these action
        $this->middware(['auth', 'isAdmin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get all user order by name and paginate with 10
        $users = User::orderby('name', 'desc')->paginate(10);
        return view('users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Get all role and pass it to the view
        $roles = Role::all();
        return view('users.create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'name'  => 'required|max:120',
            'password'  => 'required|min:6|confirmed',
        ]);

        $user = User::create($request->only('email', 'name', 'password'));

        //Check request has any roles
        if(isset($request['roles'])) {
            foreach($request['roles'] as $role) {
                $roleReg = Role::where('id', '=', $role)->firstOrFail();
                $user->assignRole($role); //Assign role for user
            }
        }

        return redirect()->route('users.index')->with('flash_message', 'User '.$user->name.' has created successfull!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect('users');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::firstOrFail($id);
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
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
        //Validate name, email, passowrd
        $this->validate($request, [
            'name'  => 'required|max:120',
            'email' => 'required|email|unique:users',
            'password'  => 'required|min:6|confirm',
        ]);

        $roles = $request['roles'];

        $input = $request->only('name', 'email', 'password');
        $user->fill($input)->save();

        if (isset($roles)) {        
            $user->roles()->sync($roles);          
        }        
        else {
            $user->roles()->detach(); 
        }
        return redirect()->route('users.index')
            ->with('flash_message',
             'User successfully edited.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id); 
        $user->delete();

        return redirect()->route('users.index')
            ->with('flash_message',
             'User successfully deleted.');
    }
    }
}
