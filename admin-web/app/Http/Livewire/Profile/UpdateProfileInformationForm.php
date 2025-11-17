<?php

namespace App\Http\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProfileInformationForm extends Component
{
    use WithFileUploads;

    public $state = [];
    public $photo;

    public function mount()
    {
        $this->state = [
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ];
    }

    public function updateProfileInformation()
    {
        $this->validate([
            'state.name' => ['required', 'string', 'max:255'],
            'state.email' => ['required', 'email', 'max:255'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $this->state['name'];
        $user->email = $this->state['email'];
        $user->save();

        $this->dispatch('saved');
    }

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function render()
    {
        return view('livewire.profile.update-profile-information-form');
    }
}
