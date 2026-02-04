<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validation du paramètre auth avec les règles Laravel
        $validator = Validator::make($request->all(), [
            'auth' => 'required|array',
            'auth.email' => 'required|string|email',
            'auth.password' => 'required|string',
        ], [
            'auth.required' => 'Le paramètre auth est requis dans le body de la requête',
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
                'error' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer les valeurs validées
        $email = $request->input('auth.email');
        $password = $request->input('auth.password');

        // Chercher l'utilisateur par email
        $user = User::where('email', $email)->first();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'error' => 'Utilisateur non trouvé'
            ], 401);
        }

        // Vérifier le mot de passe (comparaison directe des hashs)
        // Si le hash envoyé ne correspond pas, essayer aussi avec Hash::check au cas où
        // le mot de passe serait envoyé en clair
        if ($password !== $user->password && !Hash::check($password, $user->password)) {
            return response()->json([
                'error' => 'Mot de passe incorrect'
            ], 401);
        }

        // Ajouter l'utilisateur à la requête pour utilisation ultérieure si nécessaire
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}
