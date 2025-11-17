<?php

namespace App\Http\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public $confirmingUserDeletion = false;
    public $password = '';

    public function confirmUserDeletion()
    {
        $this->confirmingUserDeletion = true;
    }

    public function deleteUser()
    {
        if (!Hash::check($this->password, Auth::user()->password)) {
            $this->addError('password', __('This password does not match our records.'));
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        Auth::logout();
        $user->delete();

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.profile.delete-user-form');
    }
}
