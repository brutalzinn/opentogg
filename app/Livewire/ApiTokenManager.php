<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ApiTokenManager extends Component
{
    public string $tokenName = '';

    public ?string $newToken = null;

    public function create(): void
    {
        $this->validate([
            'tokenName' => 'required|string|max:255',
        ]);

        $token = Auth::user()->createToken($this->tokenName);

        $this->newToken = $token->plainTextToken;
        $this->tokenName = '';
    }

    public function revoke(int $tokenId): void
    {
        Auth::user()->tokens()->where('id', $tokenId)->delete();
    }

    public function dismissToken(): void
    {
        $this->newToken = null;
    }

    public function render()
    {
        $tokens = Auth::user()->tokens()->orderByDesc('created_at')->get();

        return view('livewire.api-token-manager', [
            'tokens' => $tokens,
        ]);
    }
}
