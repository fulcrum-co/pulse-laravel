<?php

namespace App\Livewire\Marketplace;

use App\Models\SellerProfile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class SellerProfileCreate extends Component
{
    use WithFileUploads;

    public string $displayName = '';
    public string $bio = '';
    public string $sellerType = 'individual';
    public $avatar;
    public array $expertiseAreas = [];
    public array $credentials = [];

    // Credential form fields
    public string $newCredentialTitle = '';
    public string $newCredentialIssuer = '';
    public string $newCredentialYear = '';

    protected $rules = [
        'displayName' => 'required|string|min:2|max:100',
        'bio' => 'nullable|string|max:1000',
        'sellerType' => 'required|in:individual,organization,verified_educator',
        'avatar' => 'nullable|image|max:2048',
        'expertiseAreas' => 'nullable|array|max:10',
        'expertiseAreas.*' => 'string|max:50',
    ];

    public function mount()
    {
        // Check if user already has a seller profile
        $existingProfile = SellerProfile::where('user_id', auth()->id())->first();
        if ($existingProfile) {
            return redirect()->route('marketplace.seller.dashboard');
        }

        // Pre-fill with user's name
        $this->displayName = auth()->user()->name ?? '';
    }

    public function addExpertise(string $area): void
    {
        $area = trim($area);
        if ($area && !in_array($area, $this->expertiseAreas) && count($this->expertiseAreas) < 10) {
            $this->expertiseAreas[] = $area;
        }
    }

    public function removeExpertise(int $index): void
    {
        unset($this->expertiseAreas[$index]);
        $this->expertiseAreas = array_values($this->expertiseAreas);
    }

    public function addCredential(): void
    {
        if (empty($this->newCredentialTitle)) {
            return;
        }

        $this->credentials[] = [
            'title' => $this->newCredentialTitle,
            'issuer' => $this->newCredentialIssuer,
            'year' => $this->newCredentialYear,
        ];

        $this->newCredentialTitle = '';
        $this->newCredentialIssuer = '';
        $this->newCredentialYear = '';
    }

    public function removeCredential(int $index): void
    {
        unset($this->credentials[$index]);
        $this->credentials = array_values($this->credentials);
    }

    public function create()
    {
        $this->validate();

        $user = auth()->user();

        // Handle avatar upload
        $avatarUrl = null;
        if ($this->avatar) {
            $path = $this->avatar->store('seller-avatars', 'public');
            $avatarUrl = '/storage/' . $path;
        }

        // Create the seller profile
        $profile = SellerProfile::create([
            'user_id' => $user->id,
            'org_id' => $user->org_id,
            'display_name' => $this->displayName,
            'slug' => SellerProfile::generateUniqueSlug($this->displayName),
            'bio' => $this->bio ?: null,
            'avatar_url' => $avatarUrl,
            'seller_type' => $this->sellerType,
            'expertise_areas' => !empty($this->expertiseAreas) ? $this->expertiseAreas : null,
            'credentials' => !empty($this->credentials) ? $this->credentials : null,
        ]);

        session()->flash('success', 'Your seller profile has been created! You can now start listing items on the marketplace.');

        return redirect()->route('marketplace.seller.dashboard');
    }

    public function render()
    {
        return view('livewire.marketplace.seller-profile-create', [
            'sellerTypes' => SellerProfile::getSellerTypes(),
        ])->layout('layouts.dashboard', ['title' => 'Become a Seller']);
    }
}
