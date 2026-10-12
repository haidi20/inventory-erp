<?php

namespace App\Http\Middleware;

use App\Shared\Support\CompanyScopeResolver;
use App\Http\View\Composers\SidebarComposer;
use App\Modules\Core\Platform\Services\SidebarNavigationMenuService;
use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'layouts.app';

    /**
     * Override handle untuk inject no-cache headers ke semua response HTML.
     */
    public function handle(\Illuminate\Http\Request $request, \Closure $next): mixed
    {
        $response = parent::handle($request, $next);

        if ($response instanceof \Illuminate\Http\Response || $response instanceof \Inertia\Response) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var CompanyScopeResolver $scopeResolver */
        $scopeResolver = app(CompanyScopeResolver::class);
        $currentCompanyUuid = $scopeResolver->resolveCompanyUuid($request);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'uuid' => $request->user()->uuid,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'branch_uuid' => current_branch_uuid(),
                    'company_uuid' => $currentCompanyUuid,
                    'roles' => $request->user()->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
                'api_token' => fn () => $this->resolveApiToken($request),
            ],
            'sidebar_menus' => function () use ($request) {
                $user = $request->user();
                if (!$user) return [];

                $isSuperAdmin = $user->isSuperAdmin();
                $permissions = $isSuperAdmin ? ['*'] : $user->getAllPermissions()->pluck('name')->toArray();

                $service = app(SidebarNavigationMenuService::class);
                $entity = SidebarNavigationMenuEntity::fromArray([
                    'current_path' => $request->path() === '/' ? '' : $request->path(),
                    'permission_names' => $permissions,
                ]);
                return $service->getSidebarMenus($entity);
            },
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'errors' => fn () => $request->session()->get('errors')?->getBag('default')->getMessages() ?? [],
            'status' => fn () => $request->session()->get('status'),
            'app_context' => [
                'runtime_environment' => $scopeResolver->resolveEnvironment($request),
                'company_uuid' => $currentCompanyUuid,
                'company_scope_strategy' => 'single_database_by_company',
            ],
        ];
    }

    private function resolveApiToken(Request $request): ?string
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $token = $request->session()->get('api_token');

        if (is_string($token) && $this->isValidApiTokenForUser($token, $user)) {
            return $token;
        }

        $token = auth('api')->login($user);
        $request->session()->put('api_token', $token);

        return $token;
    }

    private function isValidApiTokenForUser(string $token, User $user): bool
    {
        try {
            $tokenUser = auth('api')->setToken($token)->user();

            return $tokenUser?->getAuthIdentifier() === $user->getAuthIdentifier();
        } catch (\Throwable) {
            return false;
        }
    }
}
