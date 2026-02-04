<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tester l'authentification
     * 
     * Endpoint de test pour vérifier vos identifiants avant d'utiliser les autres endpoints.
     * Cet endpoint ne nécessite pas d'authentification et permet de tester si vos identifiants sont corrects.
     * 
     * **Utilisation :** Utilisez cet endpoint pour obtenir un exemple de body JSON valide que vous pouvez copier-coller dans les autres endpoints de Scramble.
     * 
     * **Validation automatique :** Le paramètre `auth` est validé automatiquement :
     * - `auth` doit être un objet
     * - `auth.email` doit être une adresse email valide
     * - `auth.password` doit être une chaîne de caractères
     * 
     * @bodyParam auth object required Objet contenant les identifiants d'authentification. Example: {"email": "test@example.com", "password": "password"}
     * @bodyParam auth.email string required Email de l'utilisateur. Example: test@example.com
     * @bodyParam auth.password string required Mot de passe hashé ou en clair de l'utilisateur. Example: password
     */
    public function test(Request $request): JsonResponse
    {
        // Validation du paramètre auth avec les règles Laravel
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'auth' => 'required|array',
            'auth.email' => 'required|string|email',
            'auth.password' => 'required|string',
        ], [
            'auth.required' => 'Le paramètre auth est requis',
            'auth.array' => 'Le paramètre auth doit être un objet',
            'auth.email.required' => 'La clé email est requise dans le paramètre auth',
            'auth.email.string' => 'La clé email doit être une chaîne de caractères',
            'auth.email.email' => 'La clé email doit être une adresse email valide',
            'auth.password.required' => 'La clé password est requise dans le paramètre auth',
            'auth.password.string' => 'La clé password doit être une chaîne de caractères',
        ]);

        // Si la validation échoue, retourner les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
                'example_body' => [
                    'auth' => [
                        'email' => 'test@example.com',
                        'password' => 'password'
                    ]
                ]
            ], 422);
        }

        // Récupérer les valeurs validées
        $email = $request->input('auth.email');
        $password = $request->input('auth.password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
                'email' => $email
            ], 401);
        }

        // Vérifier le mot de passe
        $isValid = ($password === $user->password || Hash::check($password, $user->password));

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect',
                'email' => $email
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Authentification réussie',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'body_example' => [
                'auth' => [
                    'email' => $user->email,
                    'password' => 'Votre mot de passe (hash ou en clair)'
                ]
            ],
            'note' => 'Vous pouvez maintenant utiliser ce body JSON dans les autres endpoints'
        ], 200);
    }
}
