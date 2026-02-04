<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Liste des entreprises
     * 
     * Récupère une liste paginée des entreprises avec leurs factures associées.
     * 
     * **Authentification requise :** Chaque requête doit inclure dans le body JSON le paramètre `auth` avec les identifiants de l'utilisateur.
     * 
     * **💡 Astuce pour Scramble :** Testez d'abord vos identifiants avec `POST /api/auth/test` puis copiez le body JSON dans cet endpoint.
     * 
     * **Body JSON requis :**
     * ```json
     * {
     *   "auth": {
     *     "email": "test@example.com",
     *     "password": "password"
     *   }
     * }
     * ```
     * 
     * Le système valide automatiquement la structure du paramètre `auth` (email valide, champs requis).
     * Le système vérifie que l'utilisateur existe dans la base de données avant de répondre.
     * Le mot de passe peut être envoyé en clair ou hashé (le système gère les deux formats).
     * 
     * **Paramètres de tri disponibles :** id, name, code, created_at, updated_at
     * 
     * @bodyParam auth object required Objet contenant les identifiants d'authentification. Example: {"email": "test@example.com", "password": "password"}
     * @bodyParam auth.email string required Email de l'utilisateur pour l'authentification. Example: test@example.com
     * @bodyParam auth.password string required Mot de passe (en clair ou hashé) de l'utilisateur pour l'authentification. Example: password
     * @queryParam search string Recherche par nom d'entreprise (recherche partielle). Example: Acme
     * @queryParam sort_by string Colonne de tri. Valeurs autorisées: id, name, code, created_at, updated_at. Default: id. Example: name
     * @queryParam sort_order string Ordre de tri. Valeurs: asc (croissant) ou desc (décroissant). Default: asc. Example: desc
     * @queryParam per_page int Nombre d'éléments par page (min: 1, max: 100). Default: 15. Example: 20
     */
    public function index(Request $request): JsonResponse
    {
        // Validation pour Scramble (la validation réelle est faite dans le middleware)
        $request->validate([
            'auth' => 'required|array',
            'auth.email' => 'required|string|email',
            'auth.password' => 'required|string',
        ]);

        $query = Company::query()->with('billings');

        // Recherche par nom
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Tri avec validation des colonnes autorisées
        $allowedSortColumns = ['id', 'name', 'code', 'created_at', 'updated_at'];
        $sortBy = $request->get('sort_by', 'id');
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'id';
        
        $sortOrder = strtolower($request->get('sort_order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';
        
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min(max(1, (int)$perPage), 100); // Limite entre 1 et 100
        $companies = $query->paginate($perPage);

        return response()->json($companies);
    }

    /**
     * Liste des factures d'une entreprise
     * 
     * Récupère toutes les factures d'une entreprise spécifique avec système de tri et filtres avancés.
     * 
     * **Authentification requise :** Chaque requête doit inclure dans le body JSON le paramètre `auth` avec les identifiants de l'utilisateur.
     * 
     * **💡 Astuce pour Scramble :** Testez d'abord vos identifiants avec `POST /api/auth/test` puis copiez le body JSON dans cet endpoint.
     * 
     * **Body JSON requis :**
     * ```json
     * {
     *   "auth": {
     *     "email": "test@example.com",
     *     "password": "password"
     *   }
     * }
     * ```
     * 
     * Le mot de passe peut être envoyé en clair ou hashé (le système gère les deux formats).
     * 
     * **Paramètres de tri disponibles :** id, code, amount, year, status, payment_state, type, created_at, updated_at
     * 
     * **Exemple de requête complète :**
     * ```
     * GET /api/companies/1/billings?status=active&payment_state=pending&sort_by=amount&sort_order=desc&per_page=20
     * Body: {"auth": {"email": "test@example.com", "password": "password"}}
     * ```
     * 
     * @bodyParam auth object required Objet contenant les identifiants d'authentification. Example: {"email": "test@example.com", "password": "password"}
     * @bodyParam auth.email string required Email de l'utilisateur pour l'authentification. Example: test@example.com
     * @bodyParam auth.password string required Mot de passe (en clair ou hashé) de l'utilisateur pour l'authentification. Example: password
     * @urlParam company int required ID de l'entreprise dont on souhaite récupérer les factures. Example: 1
     * @queryParam status string Filtrer par statut. Valeurs: active, inactive. Example: active
     * @queryParam payment_state string Filtrer par état de paiement. Valeurs: pending, partial, paid. Example: pending
     * @queryParam type string Filtrer par type de facture. Valeurs: icpe, lce, port. Example: icpe
     * @queryParam year int Filtrer par année de la facture. Example: 2024
     * @queryParam search string Recherche par code de facture (recherche partielle). Example: BILL-2024
     * @queryParam sort_by string Colonne de tri. Valeurs autorisées: id, code, amount, year, status, payment_state, type, created_at, updated_at. Default: id. Example: amount
     * @queryParam sort_order string Ordre de tri. Valeurs: asc (croissant) ou desc (décroissant). Default: asc. Example: desc
     * @queryParam per_page int Nombre d'éléments par page (min: 1, max: 100). Default: 15. Example: 20
     */
    public function billings(Request $request, Company $company): JsonResponse
    {
        // Validation pour Scramble (la validation réelle est faite dans le middleware)
        $request->validate([
            'auth' => 'required|array',
            'auth.email' => 'required|string|email',
            'auth.password' => 'required|string',
        ]);

        $query = $company->billings();

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par état de paiement
        if ($request->has('payment_state')) {
            $query->where('payment_state', $request->payment_state);
        }

        // Filtre par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par année
        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        // Recherche par code
        if ($request->has('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        // Tri avec validation des colonnes autorisées
        $allowedSortColumns = ['id', 'code', 'amount', 'year', 'status', 'payment_state', 'type', 'created_at', 'updated_at'];
        $sortBy = $request->get('sort_by', 'id');
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'id';
        
        $sortOrder = strtolower($request->get('sort_order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';
        
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min(max(1, (int)$perPage), 100); // Limite entre 1 et 100
        $billings = $query->paginate($perPage);

        return response()->json([
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
            ],
            'billings' => $billings,
        ]);
    }
}
