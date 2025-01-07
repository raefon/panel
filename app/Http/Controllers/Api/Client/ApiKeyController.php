<?php

namespace Kubectyl\Http\Controllers\Api\Client;

use Kubectyl\Models\ApiKey;
use Kubectyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Kubectyl\Exceptions\DisplayException;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;
use Kubectyl\Transformers\Api\Client\ApiKeyTransformer;
use Kubectyl\Http\Requests\Api\Client\Account\StoreApiKeyRequest;

class ApiKeyController extends ClientApiController
{
    /**
     * Returns all the API keys that exist for the given client.
     */
    public function index(ClientApiRequest $request): array
    {
        return $this->fractal->collection($request->user()->apiKeys)
            ->transformWith($this->getTransformer(ApiKeyTransformer::class))
            ->toArray();
    }

    /**
     * Store a new API key for a user's account.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     */
    public function store(StoreApiKeyRequest $request): array
    {
        if ($request->user()->apiKeys->count() >= 25) {
            throw new DisplayException('You have reached the account limit for number of API keys.');
        }

        $token = $request->user()->createToken(
            $request->input('description'),
            $request->input('allowed_ips')
        );

        Activity::event('user:api-key.create')
            ->subject($token->accessToken)
            ->property('identifier', $token->accessToken->identifier)
            ->log();

        return $this->fractal->item($token->accessToken)
            ->transformWith($this->getTransformer(ApiKeyTransformer::class))
            ->addMeta(['secret_token' => $token->plainTextToken])
            ->toArray();
    }

    /**
     * Deletes a given API key.
     */
    public function delete(ClientApiRequest $request, string $identifier): JsonResponse
    {
        /** @var \Kubectyl\Models\ApiKey $key */
        $key = $request->user()->apiKeys()
            ->where('key_type', ApiKey::TYPE_ACCOUNT)
            ->where('identifier', $identifier)
            ->firstOrFail();

        Activity::event('user:api-key.delete')
            ->property('identifier', $key->identifier)
            ->log();

        $key->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}