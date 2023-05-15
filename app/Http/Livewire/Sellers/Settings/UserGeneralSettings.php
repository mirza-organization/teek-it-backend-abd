<?php

namespace App\Http\Livewire\Sellers\Settings;

use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Exception;
use Illuminate\Support\Facades\Hash;
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
        public function rules()
        {
            return [
                'old_password' => 'required|min:8',
                'new_password' => 'required|min:8'
            ];
        }
        public function passwordUpdate()
        {
        try {
            $old_password = $this->old_password;
            $new_password = $this->new_password;
            $user = User::find(Auth::id());
            if (Hash::check($old_password, $user->password)) {
                $user->password = Hash::make($new_password);
               $updated = $user->save();
            sleep(1);
            if ($updated) {
                session()->flash('success', 'Your password has been updated successfully.');
            } else {
                session()->flash('error','Your old password is incorrect.');
            }
        }
        }catch (Exception $error) {
            session()->flash('error', $error);
        }
            
        }
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

    public function setUserInfo()
    {
        $user = User::find(Auth::id());
        $this->name = $user->name;
        $this->l_name = $user->l_name;
        $this->email = $user->email;
        $this->business_name = $user->business_name;
        $this->business_phone = $user->business_phone;
        $this->phone = $user->phone;
        $this->l_name = $user->l_name;
        
        return $user;
    }

    public function render()
    {
        $user = $this->setUserInfo();
        $business_hours = $user->business_hours;
        $address = $user->address_1;
        $business_location = $user->business_location;
        return view('livewire.sellers.settings.user-general-settings', compact('business_hours', 'address', 'business_location', 'user'));
    }
}
