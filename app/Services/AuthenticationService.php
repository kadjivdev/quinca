<?php

namespace App\Services;

use App\Models\Securite\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthenticationService
{
    /**
     * Authentifier un utilisateur
     *
     * @param string $email
     * @param string $password
     * @param boolean $remember
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte a été désactivé. Contactez l\'administrateur.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Auth::login($user, $remember);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Enregistrer un nouvel utilisateur
     *
     * @param array $userData
     * @return User
     */
    public function register(array $userData): User
    {
        $userData['password'] = Hash::make($userData['password']);

        $user = User::create($userData);

        event(new Registered($user));

        return $user;
    }

    /**
     * Déconnecter l'utilisateur
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();
    }

    /**
     * Envoyer le lien de réinitialisation du mot de passe
     *
     * @param string $email
     * @return array
     */
    public function sendPasswordResetLink(string $email): array
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ['status' => __($status)];
    }

    /**
     * Réinitialiser le mot de passe
     *
     * @param array $data
     * @return array
     */
    public function resetPassword(array $data): array
    {
        $status = Password::reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ['status' => __($status)];
    }

    /**
     * Vérifier si l'utilisateur est authentifié
     *
     * @return bool
     */
    public function check(): bool
    {
        return Auth::check();
    }

    /**
     * Obtenir l'utilisateur actuellement authentifié
     *
     * @return User|null
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    /**
     * Activer/Désactiver un compte utilisateur
     *
     * @param User $user
     * @param bool $status
     * @return User
     */
    public function toggleUserStatus(User $user, bool $status): User
    {
        $user->update(['is_active' => $status]);

        if (!$status) {
            $user->tokens()->delete();
        }

        return $user->fresh();
    }

     /**
     * Tenter de connecter l'utilisateur
     *
     * @param array $credentials
     * @return bool
     */
    public function attemptLogin(array $credentials): bool
    {
        // Vous pouvez ajouter votre logique personnalisée ici
        // Par exemple, vérifier si l'utilisateur est actif

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte a été désactivé.']
            ]);
        }

        // Connexion réussie
        Auth::login($user);

        return true;
    }


    /**
     * Vérifier les permissions de l'utilisateur
     *
     * @param User $user
     * @param string|array $permissions
     * @return bool
     */
    public function checkPermissions(User $user, $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }
}
