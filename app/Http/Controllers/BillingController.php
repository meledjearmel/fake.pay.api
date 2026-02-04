<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Liste des factures
     * 
     * Récupère une liste paginée de toutes les factures avec leurs entreprises associées.
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
     * **Paramètres de tri disponibles :** id, code, amount, year, status, payment_state, type, providence, is_registered, created_at, updated_at, edited_at, last_paid_at
     * 
     * **Exemple de requête complète :**
     * ```
     * GET /api/billings?company_id=1&status=active&payment_state=pending&sort_by=amount&sort_order=desc
     * Body: {"auth": {"email": "test@example.com", "password": "password"}}
     * ```
     * 
     * @bodyParam auth object required Objet contenant les identifiants d'authentification. Example: {"email": "test@example.com", "password": "password"}
     * @bodyParam auth.email string required Email de l'utilisateur pour l'authentification. Example: test@example.com
     * @bodyParam auth.password string required Mot de passe (en clair ou hashé) de l'utilisateur pour l'authentification. Example: password
     * @queryParam company_id int Filtrer par ID d'entreprise. Example: 1
     * @queryParam status string Filtrer par statut. Valeurs: active, inactive. Example: active
     * @queryParam payment_state string Filtrer par état de paiement. Valeurs: pending, partial, paid. Example: pending
     * @queryParam type string Filtrer par type de facture. Valeurs: icpe, lce, port. Example: icpe
     * @queryParam year int Filtrer par année de la facture. Example: 2024
     * @queryParam search string Recherche par code de facture (recherche partielle). Example: BILL-2024
     * @queryParam sort_by string Colonne de tri. Valeurs autorisées: id, code, amount, year, status, payment_state, type, providence, is_registered, created_at, updated_at, edited_at, last_paid_at. Default: id. Example: amount
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

        $query = Billing::query()->with('company');

        // Filtre par entreprise
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

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
        $allowedSortColumns = ['id', 'code', 'amount', 'year', 'status', 'payment_state', 'type', 'providence', 'is_registered', 'created_at', 'updated_at', 'edited_at', 'last_paid_at'];
        $sortBy = $request->get('sort_by', 'id');
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'id';
        
        $sortOrder = strtolower($request->get('sort_order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';
        
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min(max(1, (int)$perPage), 100); // Limite entre 1 et 100
        $billings = $query->paginate($perPage);

        return response()->json($billings);
    }

    /**
     * Afficher une facture
     * 
     * Récupère les détails complets d'une facture spécifique avec son entreprise associée.
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
     * **Exemple de requête :**
     * ```
     * GET /api/billings/1
     * Body: {"auth": {"email": "test@example.com", "password": "password"}}
     * ```
     * 
     * @bodyParam auth object required Objet contenant les identifiants d'authentification. Example: {"email": "test@example.com", "password": "password"}
     * @bodyParam auth.email string required Email de l'utilisateur pour l'authentification. Example: test@example.com
     * @bodyParam auth.password string required Mot de passe (en clair ou hashé) de l'utilisateur pour l'authentification. Example: password
     * @urlParam billing int required ID de la facture à récupérer. Example: 1
     */
    public function show(Request $request, Billing $billing): JsonResponse
    {
        // Validation pour Scramble (la validation réelle est faite dans le middleware)
        $request->validate([
            'auth' => 'required|array',
            'auth.email' => 'required|string|email',
            'auth.password' => 'required|string',
        ]);

        $billing->load('company');

        return response()->json($billing);
    }

    /**
     * Mettre à jour une facture
     * 
     * Met à jour uniquement les champs modifiables d'une facture existante : `is_registered`, `payment_state` et `last_paid_at`.
     * 
     * **⚠️ Important :** Seuls ces trois champs peuvent être modifiés. Les autres champs (code, amount, year, etc.) ne peuvent pas être modifiés via cet endpoint.
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
     *   },
     *   "is_registered": true,
     *   "payment_state": "paid",
     *   "last_paid_at": "2024-01-20 14:00:00"
     * }
     * ```
     * 
     * Le mot de passe peut être envoyé en clair ou hashé (le système gère les deux formats).
     * 
     * **Exemple de requête complète :**
     * ```
     * PUT /api/billings/1
     * Body: {
     *   "auth": {
     *     "email": "test@example.com",
     *     "password": "password"
     *   },
     *   "is_registered": true,
     *   "payment_state": "paid",
     *   "last_paid_at": "2024-01-20 14:00:00"
     * }
     * ```
     * 
     * **Valeurs acceptées pour payment_state :** pending, partial, paid
     * 
     * @bodyParam auth object required Objet contenant les identifiants d'authentification. Example: {"email": "test@example.com", "password": "password"}
     * @bodyParam auth.email string required Email de l'utilisateur pour l'authentification. Example: test@example.com
     * @bodyParam auth.password string required Mot de passe (en clair ou hashé) de l'utilisateur pour l'authentification. Example: password
     * @urlParam billing int required ID de la facture à mettre à jour. Example: 1
     * @bodyParam is_registered boolean Indique si la facture est enregistrée. Valeurs: true, false. Example: true
     * @bodyParam payment_state string État de paiement de la facture. Valeurs: pending, partial, paid. Example: paid
     * @bodyParam last_paid_at string Date et heure du dernier paiement au format datetime (YYYY-MM-DD HH:mm:ss) ou date ISO. Example: 2024-01-20 14:00:00
     */
    public function update(Request $request, Billing $billing): JsonResponse
    {
        // Validation pour Scramble (la validation réelle est faite dans le middleware)
        $request->validate([
            'auth' => 'required|array',
            'auth.email' => 'required|string|email',
            'auth.password' => 'required|string',
            'is_registered' => 'sometimes|boolean',
            'payment_state' => 'sometimes|in:pending,partial,paid',
            'last_paid_at' => 'sometimes|nullable|date',
        ]);

        // Valider et récupérer uniquement les champs modifiables (exclure auth)
        $validated = $request->validate([
            'is_registered' => 'sometimes|boolean',
            'payment_state' => 'sometimes|in:pending,partial,paid',
            'last_paid_at' => 'sometimes|nullable|date',
        ]);

        $billing->update($validated);
        $billing->load('company');

        return response()->json($billing);
    }
}
