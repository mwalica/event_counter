<?php

use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
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

<div>
    <flux:heading level="3" size="xl" class="text-center font-semibold">Login</flux:heading>
    <form wire:submit="authenticate()">
        <flux:input type="email" wire:model="email" label="E-mail"/>
        <flux:input type="password" wire:model="password" label="Password"/>
        <flux:button type="submit" variant="primary" class="w-fit cursor-pointer">Login</flux:button>
    </form>


    @if($loginMessage)
        <flux:text>
            {{$loginMessage}}
        </flux:text>
    @endisset
</div>
