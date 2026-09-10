<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserFormComponent extends Component
{
    public ?User $user = null;
    public bool $isEditing = false;

    public ?int $branchId = null;
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public array $selectedRoles = [];
    public bool $isActive = true;

    public array $branches = [];
    public array $roles = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'nullable|string|min:8|confirmed',
        'branchId' => 'nullable|exists:branches,id',
        'isActive' => 'boolean',
    ];

    public function mount(?int $userId = null): void
    {
        $this->branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'display_name'])
            ->toArray();

        if ($userId) {
            $this->loadUser($userId);
        }
    }

    public function loadUser(int $userId): void
    {
        $this->user = User::with('roles')->findOrFail($userId);
        $this->isEditing = true;
        $this->branchId = $this->user->branch_id;
        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->isActive = $this->user->is_active;
        $this->selectedRoles = $this->user->roles->pluck('id')->toArray();
        $this->password = '';
        $this->passwordConfirmation = '';
    }

    public function updatedUsername(): void
    {
        $this->validateOnly('username', [
            'username' => 'required|string|max:255|unique:users,username' . ($this->isEditing ? ",{$this->user->id}" : ''),
        ]);
    }

    public function updatedEmail(): void
    {
        $this->validateOnly('email', [
            'email' => 'required|email|max:255|unique:users,email' . ($this->isEditing ? ",{$this->user->id}" : ''),
        ]);
    }

    public function store(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'branchId' => 'nullable|exists:branches,id',
            'isActive' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'Password123!'),
                'branch_id' => $validated['branchId'],
                'is_active' => $validated['isActive'],
                'created_by' => auth()->id(),
            ]);

            if (! empty($validated['selectedRoles'] ?? [])) {
                foreach ($validated['selectedRoles'] as $roleId) {
                    $user->assignRole(Role::find($roleId));
                }
            }
        });

        session()->flash('success', 'User berhasil ditambahkan.');
        return redirect()->route('users.index');
    }

    public function update(): void
    {
        if (! $this->user) {
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'branchId' => 'nullable|exists:branches,id',
            'isActive' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $this->user->update([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'branch_id' => $validated['branchId'],
                'is_active' => $validated['isActive'],
            ]);

            if (! empty($validated['password'])) {
                $this->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            // Sync roles
            $this->user->roles()->detach();
            if (! empty($validated['selectedRoles'] ?? [])) {
                foreach ($validated['selectedRoles'] as $roleId) {
                    $this->user->assignRole(Role::find($roleId));
                }
            }
        });

        session()->flash('success', 'User berhasil diperbarui.');
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.user-form-component');
    }
}