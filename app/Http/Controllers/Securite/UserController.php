<?php

namespace App\Http\Controllers\Securite;

use App\Http\Controllers\Controller;
use App\Models\Securite\{User, Role};
use App\Models\Parametre\PointDeVente;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
        // Retirez le middleware sanctum d'ici car il interfère avec la page de login
        $this->middleware('auth')->except([
            'showLogin',
            'login'
        ]);
    }

    public function index()
    {
        $users = User::with(['roles', 'pointDeVente'])->get();
        $roles = Role::all();
        $pointsDeVente = PointDeVente::all();
        return view('pages.securite.users.index', compact('users', 'roles', 'pointsDeVente'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roles' => 'required',
            'point_de_vente_id' => 'required|exists:point_de_ventes,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'point_de_vente_id' => $request->point_de_vente_id
        ]);

        $user->assignRole($request->input('roles'));

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès'
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'roles' => 'required',
            // 'point_de_vente_id' => 'required|exists:point_de_ventes,id'
        ]);

        $user = User::find($id);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            // 'point_de_vente_id' => $request->point_de_vente_id
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->assignRole($request->input('roles'));

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès'
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    public function show($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            $currentUser = auth()->user();

            $isSuperAdmin = false;
            if ($currentUser && $currentUser->roles) {
                $isSuperAdmin = $currentUser->roles->contains('name', 'super-admin');
            }

            return response()->json([
                'user' => $user,
                'roles' => $user->roles,
                'currentUserIsSuperAdmin' => $isSuperAdmin
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de charger les détails de l\'utilisateur'
            ], 404);
        }
    }

    public function checkEmailAvailability(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'available' => !$exists
        ]);
    }

    /**
     * Afficher la page de connexion
     *
     * @return View|RedirectResponse
     */
    public function showLogin()
    {
        // Si l'utilisateur est déjà connecté, on le redirige
        if (auth()->check()) {
            return redirect()->intended(config('app.url') . '/portail');
        }

        return view('pages.securite.Login');
    }

    /**
     * Authentifier un utilisateur
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $credentials = [
                'email' => $request->username, // ou adaptez selon votre logique d'authentification
                'password' => $request->password
            ];

            $redirectUrl = session()->pull('url.intended', '/portail');

            if ($this->authService->attemptLogin($credentials)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Connexion réussie',
                    'redirect' => $redirectUrl
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants incorrects'
            ], 401);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la connexion',
                'debug' => $e->getMessage() // À retirer en production
            ], 500);
        }
    }

    /**
     * Enregistrer un nouvel utilisateur
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'point_vente_id' => 'required|exists:point_ventes,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = $this->authService->register($validator->validated());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Compte créé avec succès',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la création du compte'
            ], 500);
        }
    }

    /**
     * Déconnecter l'utilisateur
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return redirect()->route('login-portail');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * Obtenir le profil de l'utilisateur connecté
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('pointVente');

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération du profil'
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil utilisateur
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . $request->user()->id,
                'password' => 'nullable|string|min:8|confirmed',
                'point_vente_id' => 'exists:point_ventes,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = $this->authService->updateProfile(
                $request->user(),
                $validator->validated()
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Profil mis à jour avec succès',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * Envoyer le lien de réinitialisation du mot de passe
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = $this->authService->sendPasswordResetLink($request->email);

            return response()->json([
                'status' => 'success',
                'message' => 'Le lien de réinitialisation a été envoyé'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'envoi du lien'
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = $this->authService->resetPassword($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la réinitialisation'
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un compte utilisateur
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleStatus(User $user, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $this->authService->toggleUserStatus($user, $request->status);

            return response()->json([
                'status' => 'success',
                'message' => 'Statut mis à jour avec succès',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour du statut'
            ], 500);
        }
    }
}
