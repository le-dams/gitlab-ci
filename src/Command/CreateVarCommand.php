<?php

namespace GitLab\Command;

use Gitlab\GitLab;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;

class CreateVarCommand extends Command
{
    public function getName(): string
    {
        return __CLASS__;
    }

    public function exec(GitLab $gitLab, string $key, string $value, string $env): void
    {
        foreach ($gitLab->getProjects() as $id => $name) {
            try {
                echo strtoupper("\n{$name}:\n");
                echo "Check CI {$key} variable for project {$name} on env: {$env}\n";

                $variables = $gitLab->call('GET', "/api/v4/projects/{$id}/variables/{$key}?filter[environment_scope]={$env}");

                if (!is_null($variables)) {
                    if ($variables['value'] !== $value) {
                        echo "{$key} found in project {$name} with value {$variables['value']} - update with value {$value} on going\n";

                        $gitLab->call('PUT', "/api/v4/projects/{$id}/variables/{$key}", [
                            'filter' => [
                                'environment_scope' => $env,
                            ],
                            'value' => $value,
                        ]);
                    } else {
                        echo "{$key} already has the right value for project {$name}\n";
                    }
                }
            } catch (InvalidArgumentException $exception) {
                if (404 === $exception->getCode()) {
                    echo "{$key} not found in project {$name} - creation on going\n";

                    $gitLab->call('POST', "/api/v4/projects/{$id}/variables", [
                        'key' => $key,
                        'value' => $value,
                        'environment_scope' => $env,
                    ]);
                } else {
                    throw $exception;
                }
            }
        }
    }
}
