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

        public function exec(GitLab $gitLab, string $branch, string $tag, ?string $message = null): void
        {
            foreach ($gitLab->getProjects() as $id => $name) {
                try {
                    echo strtoupper("\n{$name}:\n");
                    echo "Check if tag {$tag} exist.\n";

                    $gitLab->call('GET', '/api/v4/projects/' . $id . '/repository/tags/' . $tag);
                } catch (\InvalidArgumentException $exception) {
                    if (404 === $exception->getCode()) {
                        echo "Tag {$tag} does not exist.\n";
                        echo "Create tag {$tag} on branch {$branch}.\n";

                        $gitLab->call('POST', '/api/v4/projects/' . $id . '/repository/tags', [
                            'tag_name' => $tag,
                            'ref' => $branch,
                            'message' => $message ?? 'New automation tag',
                        ]);
                    } else {
                        throw $exception;
                    }
                }
            }
        }
    }

