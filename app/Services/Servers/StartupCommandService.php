<?php

namespace Kubectyl\Services\Servers;

use Kubectyl\Models\Server;

class StartupCommandService
{
    /**
     * Generates a startup command for a given server instance.
     */
    public function handle(Server $server, bool $hideAllValues = false): string
    {
        $find = ['{{SERVER_MEMORY}}', '{{SERVER_PORT}}'];
        $replace = [$server->memory, $server->default_port];

        foreach ($server->variables as $variable) {
            $find[] = '{{' . $variable->env_variable . '}}';
            $replace[] = ($variable->user_viewable && !$hideAllValues) ? ($variable->server_value ?? $variable->default_value) : '[hidden]';
        }

        return str_replace($find, $replace, $server->startup);
    }
}
