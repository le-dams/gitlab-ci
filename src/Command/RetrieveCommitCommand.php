<?php

    namespace GitLab\Command;

    use Gitlab\GitLab;
    use Symfony\Component\Console\Command\Command;

    class RetrieveCommitCommand extends Command
    {
        const DEFAULT_BRANCH = 'master';

        public function getName(): string
        {
            return __CLASS__;
        }

        private function getProjectInfo(GitLab $gitLab, int $id): array
        {
            try {
                return $gitLab->call('GET', '/api/v4/projects/' . $id);
            } catch (\Exception $exception) {
                return [];
            }
        }

        private function getProjectBranches(GitLab $gitLab, int $id): array
        {
            try {
                return $gitLab->call('GET', '/api/v4/projects/' . $id.'/repository/branches');
            } catch (\Exception $exception) {
                return [];
            }
        }

        private function getMergeRequests(GitLab $gitLab, int $id, string $branch): array
        {
            try {
                return $gitLab->call('GET', '/api/v4/projects/' . $id.'/merge_requests?state=opened&target_branch='.urlencode($branch));
            } catch (\Exception $exception) {
                return [];
            }
        }

        private function getBranchInfo(GitLab $gitLab, int $id, string $branch, string $defaultBranch = self::DEFAULT_BRANCH): array
        {
            try {
                return $gitLab->call('GET', '/api/v4/projects/' . $id . '/repository/branches/' . urlencode($branch));
            } catch (\InvalidArgumentException $exception) {
                return $gitLab->call('GET', '/api/v4/projects/' . $id . '/repository/branches/' . urlencode($defaultBranch));
            }
        }

        public function getBranches(GitLab $gitLab): array
        {
            $branches = [];
            foreach ($gitLab->getProjects() as $id => $name) {

                try {
                    foreach ($this->getProjectBranches($gitLab, $id) as $gitlabProjectBranches) {
                        if (isset($gitlabProjectBranches['name']) && false === in_array($gitlabProjectBranches['name'], $branches)) {
                            if (false !== strpos($gitlabProjectBranches['name'], 'releases') || true === $gitlabProjectBranches['default']) {
                                $branches[] = $gitlabProjectBranches['name'];
                            }
                        }
                    }
                } catch (\InvalidArgumentException $exception) {
                }
            }
            return $branches;
        }

        public function getHashes(GitLab $gitLab, string $branch): array
        {
            $repositories = [];
            foreach ($gitLab->getProjects() as $id => $name) {
                $repository = [
                    'id' => $id,
                    'name' => $name,
                    'branch' => 'No found',
                    'commit' => [
                        'short_id' => null,
                        'created_at' => null,
                        'committer' => [
                            'name' => null,
                            'email' => null,
                        ]
                    ]
                ];

                try {
                    $gitlabProject = $this->getProjectInfo($gitLab, $id);
                    $gitlabBranch = $this->getBranchInfo($gitLab, $id, $branch, $gitlabProject['default_branch']);
                    $gitlabMergeRequests = $this->getMergeRequests($gitLab, $id, $gitlabBranch['name']);

                    $repository['branch'] = $gitlabBranch['name'];
                    $repository['merge_requests'] = $gitlabMergeRequests;
                    $repository['commit']['short_id'] = $gitlabBranch['commit']['short_id'];
                    $repository['commit']['created_at'] = date('Y-m-d H:i:s', strtotime($gitlabBranch['commit']['created_at']));
                    $repository['commit']['committer']['name'] = $gitlabBranch['commit']['committer_name'];
                    $repository['commit']['committer']['email'] = $gitlabBranch['commit']['committer_email'];

                } catch (\InvalidArgumentException $exception) {
                }

                $repositories[$id] = $repository;
            }
            return $repositories;
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

