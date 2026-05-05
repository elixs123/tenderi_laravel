<?php 
namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class Login extends Component
{
    public $email;
    public $password;
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('dashboard');
        }

        $this->addError('email', 'Podaci ne odgovaraju našim zapisima.');
    }

    #[Layout('layouts.auth')] 
    public function render()
    {
        return view('livewire.auth.login');
    }
}