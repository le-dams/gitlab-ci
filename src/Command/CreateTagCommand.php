<?php

    namespace GitLab\Command;

    use Gitlab\GitLab;
    use Symfony\Component\Console\Command\Command;

    class CreateTagCommand extends Command
    {

        public function getName(): string
        {
            return __CLASS__;
        }

        public function exec(GitLab $gitLab, string $branch, string $tag): void
        {
            foreach ($gitLab->getProjects() as $id => $name) {
                try {
                    $gitLab->call('GET', '/api/v4/projects/' . $id . '/repository/tags/' . $tag);
                } catch (\InvalidArgumentException $exception) {
                    if (404 === $exception->getCode()) {
                        $gitLab->call('POST', '/api/v4/projects/' . $id . '/repository/tags', [
                            'tag_name' => $tag,
                            'ref' => $branch,
                            'message' => 'New automation tag'
                        ]);
                    } else {
                        throw $exception;
                    }
                }
            }
        }
    }

