<?php

namespace GitLab\Command;

use Gitlab\GitLab;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;

class DeleteVarCommand extends Command
{
    public function getName(): string
    {
        return __CLASS__;
    }

    public function exec(GitLab $gitLab, string $key, ?string $env = null): void
    {
        foreach ($gitLab->getProjects() as $id => $name) {
            try {
                echo strtoupper("\n{$name}:\n");
                echo "Delete {$key} variable on env: {$env}\n";

                $uri = is_null($env)
                    ? "/api/v4/projects/{$id}/variables/{$key}"
                    : "/api/v4/projects/{$id}/variables/{$key}?filter[environment_scope]={$env}";

                $gitLab->call('DELETE', $uri);
            } catch (InvalidArgumentException $exception) {
                if (404 === $exception->getCode()) {
                    echo "{$key} not found in project {$name}\n";
                } else {
                    throw $exception;
                }
            }
        }
    }
}
