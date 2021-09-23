<?php

    namespace GitLab\Command;

    use Gitlab\GitLab;
    use Symfony\Component\Console\Command\Command;

    class CheckPipelinesCommand extends Command
    {

        public function getName(): string
        {
            return __CLASS__;
        }

        public function exec(GitLab $gitLab, string $tag): void
        {
            foreach ($gitLab->getProjects() as $id => $name) {
                try {
                    echo strtoupper("\n{$name}:\n");
                    echo "Check if pipeline with tag {$tag} is successfully done.\n";

                    $pipelines = $gitLab->call('GET', "/api/v4/projects/{$id}/pipelines?ref={$tag}");

                    if (is_countable($pipelines) && count($pipelines) > 0) {
                        $pipeline = current($pipelines);

                        if ($pipeline['status'] !== 'success') {
                            echo "\033[31m Pipeline {$pipeline['id']} failed on project: {$name}\033[39m\n";
                            echo "{$pipeline['web_url']}\n";
                        } else {
                            echo "\033[32m Pipeline {$pipeline['id']} end with success on project: {$name}\033[39m\n";
                        }
                    }
                } catch (\InvalidArgumentException $exception) {
                    if (404 === $exception->getCode()) {
                        echo "\033[31m Pipeline not found on project: {$name}.\033[39m\n";
                    } else {
                        throw $exception;
                    }
                }
            }
        }
    }

