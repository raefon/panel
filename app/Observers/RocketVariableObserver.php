<?php

namespace Kubectyl\Observers;

use Kubectyl\Models\RocketVariable;

class RocketVariableObserver
{
    public function creating(RocketVariable $variable): void
    {
        if ($variable->field_type) {
            unset($variable->field_type);
        }
    }

    public function updating(RocketVariable $variable): void
    {
        if ($variable->field_type) {
            unset($variable->field_type);
        }
    }
}
