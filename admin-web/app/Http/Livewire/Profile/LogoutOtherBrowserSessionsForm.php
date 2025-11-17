<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class LogoutOtherBrowserSessionsForm extends Component
{
    public $confirmingLogout = false;
    public $password = '';

    public function confirmLogout()
    {
        $this->confirmingLogout = true;
    }

    public function logoutOtherBrowserSessions()
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, Auth::user()->password)) {
            $this->addError('password', __('This password does not match our records.'));
            return;
        }

        Auth::logoutOtherDevices($this->password);

        $this->confirmingLogout = false;
        $this->password = '';

        $this->dispatch('loggedOut');
    }

    public function render()
    {
        return view('livewire.profile.logout-other-browser-sessions-form');
    }
}
