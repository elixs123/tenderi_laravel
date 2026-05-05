<?php 
namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Sidebar extends Component
{
    public function toggleTheme()
    {
        if(Auth::check()){
            $user = Auth::user();

            $newTheme = $user->theme === 'dark' ? 'light' : 'dark';
             
            $user->update(['theme' => $newTheme]);
        } else {
            $newTheme = 'light';
        }
       
        
        $this->dispatch('theme-updated', theme: $newTheme);
    }

    public function render()
    {
        return view('livewire.layouts.sidebar');
    }
}