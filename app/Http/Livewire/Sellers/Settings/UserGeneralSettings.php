<?php

namespace App\Http\Livewire\Sellers\Settings;

use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Exception;

class UserGeneralSettings extends Component
{
    public
        $name,
        $l_name,
        $email,
        $business_name,
        $business_phone,
        $phone,
        $old_password,
        $new_password,
        $search = '';
        
    public function update()
    {
        try {
            /* Perform some operation */
            $updated = User::where('id', Auth::id())
                ->update(['name' => $this->name, 'email' => $this->email, 'business_name' => $this->business_name, 'phone' => $this->phone, 'l_name' => $this->l_name]);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', 'User settings updated successfully!');
            } else {
                session()->flash('error', 'User settings cannot be updated!');
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function getUserInfo()
    {
    }

    public function render()
    {
        $user = User::find(Auth::id());
        $this->name = $user->name;
        $this->l_name = $user->l_name;
        $this->email = $user->email;
        $this->business_name = $user->business_name;
        $this->business_phone = $user->business_phone;
        $this->phone = $user->phone;
        $this->l_name = $user->l_name;
        $business_hours = $user->business_hours;
        $address = $user->address_1;
        $business_location = $user->business_location;
        return view('livewire.sellers.settings.user-general-settings', compact('business_hours', 'address', 'business_location', 'user'));
    }
}
