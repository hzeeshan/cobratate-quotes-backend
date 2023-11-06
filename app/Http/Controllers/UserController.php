<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function assignRole()
    {
        // Fetch the admin role
        $adminRole = Role::findByName('admin');

        // Fetch the user you want to assign the role to
        $user = User::find(1); // Replace with the correct user ID

        // Assign the role
        $user->assignRole($adminRole);

        dd("Admin role was assignd to {$user->name}");
    }
}
