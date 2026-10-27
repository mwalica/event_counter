<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new
#[Layout('layouts.login')]
#[Title('Login to EventCounter')]
class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public ?string $loginMessage = null;

    public function authenticate(): void
    {
        $this->validate();
        $valid = Auth::attempt(['email' => $this->email, 'password' => $this->password], true);

        if ($valid) {
            session()->regenerate();
            $this->loginMessage = null;
            $this->redirectIntended(navigate: true);
        } else {
            $this->loginMessage = 'Incorrect email and/or password';
        }
    }

};

?>

<div class="mx-auto max-w-3xl flex justify-center items-center h-full">
    <flux:card class="w-full h-fit -translate-y-16">
        <flux:heading level="3" size="xl" class="text-center font-semibold"><flux:icon.log-in class="inline -translate-y-[1px]" />Login</flux:heading>
        <form wire:submit="authenticate()" class="space-y-8">
            <flux:input type="email" wire:model="email" label="E-mail"/>
            <flux:input type="password" wire:model="password" label="Password"/>
            <flux:button type="submit" variant="primary" class="w-fit cursor-pointer">Login</flux:button>
        </form>


        @if($loginMessage)
            <flux:text class="mt-4">
                {{$loginMessage}}
            </flux:text>
        @endisset
    </flux:card>


</div>
