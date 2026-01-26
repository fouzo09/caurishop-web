<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\Admin\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
        // Supprimer temporairement les middlewares de permission pour tester
        // Ou ajuster selon vos besoins
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'role' => $request->get('role'),
        ];

        $users = $this->userService->getPaginatedUsers(15, $filters);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(User $user): View
    {
        $user->load('roles', 'permissions');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $this->userService->updateUser($user, $request->validated());

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            if (auth()->id() === $user->id) {
                return redirect()->back()
                    ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            }

            $this->userService->deleteUser($user);

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function suspend(User $user): RedirectResponse
    {
        try {
            if (auth()->id() === $user->id) {
                return redirect()->back()
                    ->with('error', 'Vous ne pouvez pas suspendre votre propre compte.');
            }

            $this->userService->suspendUser($user);

            return redirect()->back()
                ->with('success', 'Utilisateur suspendu avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function activate(User $user): RedirectResponse
    {
        try {
            $this->userService->activateUser($user);

            return redirect()->back()
                ->with('success', 'Utilisateur activé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function verify(User $user): RedirectResponse
    {
        try {
            $this->userService->verifyUserEmail($user);

            return redirect()->back()
                ->with('success', 'Email vérifié avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
