<?php

namespace Kubectyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Kubectyl\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Services\Api\KeyCreationService;
use Kubectyl\Contracts\Repository\ApiKeyRepositoryInterface;
use Kubectyl\Http\Requests\Admin\Api\StoreApplicationApiKeyRequest;

class ApiController extends Controller
{
    /**
     * ApiController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private ApiKeyRepositoryInterface $repository,
        private KeyCreationService $keyCreationService,
        private ViewFactory $view,
    ) {
    }

    /**
     * Render view showing all of a user's application API keys.
     */
    public function index(Request $request): View
    {
        return $this->view->make('admin.api.index', [
            'keys' => $this->repository->getApplicationKeys($request->user()),
        ]);
    }

    /**
     * Render view allowing an admin to create a new application API key.
     *
     * @throws \ReflectionException
     */
    public function create(): View
    {
        $resources = AdminAcl::getResourceList();
        sort($resources);

        return $this->view->make('admin.api.new', [
            'resources' => $resources,
            'permissions' => [
                'r' => AdminAcl::READ,
                'rw' => AdminAcl::READ | AdminAcl::WRITE,
                'n' => AdminAcl::NONE,
            ],
        ]);
    }

    /**
     * Store the new key and redirect the user back to the application key listing.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function store(StoreApplicationApiKeyRequest $request): RedirectResponse
    {
        $this->keyCreationService->setKeyType(ApiKey::TYPE_APPLICATION)->handle([
            'memo' => $request->input('memo'),
            'user_id' => $request->user()->id,
        ], $request->getKeyPermissions());

        $this->alert->success('A new application API key has been generated for your account.')->flash();

        return redirect()->route('admin.api.index');
    }

    /**
     * Delete an application API key from the database.
     */
    public function delete(Request $request, string $identifier): Response
    {
        $this->repository->deleteApplicationKey($request->user(), $identifier);

        return response('', 204);
    }
}
