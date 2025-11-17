<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TwoFactorAuthenticationForm extends Component
{
    public $showingQrCode = false;
    public $showingRecoveryCodes = false;

    public function enableTwoFactorAuthentication()
    {
        $this->showingQrCode = true;
    }

    public function disableTwoFactorAuthentication()
    {
        $this->showingQrCode = false;
    }

    public function render()
    {
        return view('livewire.profile.two-factor-authentication-form');
    }
}
