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
            $messages = [];
            foreach ($gitLab->getProjects() as $id => $name) {
                try {
                    $pipelines = $gitLab->call('GET', "/api/v4/projects/{$id}/pipelines?ref={$tag}");

                    if (is_countable($pipelines) && count($pipelines) > 0) {
                        $pipeline = current($pipelines);

                        switch ($pipeline['status']) {
                            case 'success':
                                break;
                            case 'running':
                                $messages[] = "\033[33m Pipeline {$pipeline['id']} \"".$pipeline['status']."\" on project: {$name}\033[39m\n";
                                break;
                            default:
                                $messages[] = "\033[31m Pipeline {$pipeline['id']} \"".$pipeline['status']."\" on project: {$name}\033[39m\n";
                                break;
                        }
                    }
                } catch (\InvalidArgumentException $exception) {
                    if (404 === $exception->getCode()) {
                        $messages[] = "\033[31m Pipeline not found on project: {$name}.\033[39m\n";
                    } else {
                        throw $exception;
                    }
                }
                echo ".";
            }

            if (0 === count($messages)) {
                echo "\033[32m All is completed\033[39m\n";
            }

            foreach ($messages as $message) {
                echo $message;
            }
        }
    }

