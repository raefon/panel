<?php

namespace Kubectyl\Services\Rockets\Variables;

use Kubectyl\Models\RocketVariable;
use Kubectyl\Traits\Services\ValidatesValidationRules;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Kubectyl\Contracts\Repository\RocketVariableRepositoryInterface;
use Kubectyl\Exceptions\Service\Rocket\Variable\ReservedVariableNameException;

class VariableCreationService
{
    use ValidatesValidationRules;

    /**
     * VariableCreationService constructor.
     */
    public function __construct(private RocketVariableRepositoryInterface $repository, private ValidationFactory $validator)
    {
    }

    /**
     * Return the validation factory instance to be used by rule validation
     * checking in the trait.
     */
    protected function getValidator(): ValidationFactory
    {
        return $this->validator;
    }

    /**
     * Create a new variable for a given Rocket.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Service\Rocket\Variable\BadValidationRuleException
     * @throws \Kubectyl\Exceptions\Service\Rocket\Variable\ReservedVariableNameException
     */
    public function handle(int $rocket, array $data): RocketVariable
    {
        if (in_array(strtoupper(array_get($data, 'env_variable')), explode(',', RocketVariable::RESERVED_ENV_NAMES))) {
            throw new ReservedVariableNameException(sprintf('Cannot use the protected name %s for this environment variable.', array_get($data, 'env_variable')));
        }

        if (!empty($data['rules'] ?? '')) {
            $this->validateRules($data['rules']);
        }

        $options = array_get($data, 'options') ?? [];

        return $this->repository->create([
            'rocket_id' => $rocket,
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'env_variable' => $data['env_variable'] ?? '',
            'default_value' => $data['default_value'] ?? '',
            'user_viewable' => in_array('user_viewable', $options),
            'user_editable' => in_array('user_editable', $options),
            'rules' => $data['rules'] ?? '',
        ]);
    }
}
