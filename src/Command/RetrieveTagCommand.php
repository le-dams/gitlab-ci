<?php

    namespace GitLab\Command;

    use Gitlab\GitLab;
    use Symfony\Component\Console\Command\Command;

    class RetrieveTagCommand extends Command
    {
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

        private function getProjectTags(GitLab $gitLab, int $id): array
        {
            try {
                return $gitLab->call('GET', '/api/v4/projects/' . $id.'/repository/tags');
            } catch (\Exception $exception) {
                return [];
            }
        }

        public function getTagPipeline(GitLab $gitLab, int $id, string $ref): array
        {
            try {
                return $gitLab->call('GET', "/api/v4/projects/{$id}/pipelines?ref={$ref}");
            } catch (\InvalidArgumentException $exception) {
                return [];
            }
        }

        public function getTags(GitLab $gitLab, string $tag = null): array
        {
            $projects = [];
            foreach ($gitLab->getProjects() as $id => $name) {
                $tags = [];
                try {
                    $tagInfo = null;
                    $gitlabProjectInfo = $this->getProjectInfo($gitLab, $id);
                    foreach ($this->getProjectTags($gitLab, $id) as $gitlabProjectTag) {
                        if (isset($gitlabProjectTag['name']) && false === in_array($gitlabProjectTag['name'], $tags)) {
                            $tags[] = $gitlabProjectTag['name'];
                        }

                        if ($tag && $gitlabProjectTag['name'] === $tag) {
                            $tagInfo['name'] = $gitlabProjectTag['name'];
                            $tagInfo['commit']['short_id'] = $gitlabProjectTag['commit']['short_id'];
                            $tagInfo['commit']['created_at'] = date('Y-m-d H:i:s', strtotime($gitlabProjectTag['commit']['created_at']));
                            $tagInfo['commit']['committer']['name'] = $gitlabProjectTag['commit']['committer_name'];
                            $tagInfo['commit']['committer']['email'] = $gitlabProjectTag['commit']['committer_email'];

                            foreach ($this->getTagPipeline($gitLab, $id, $tag) as $pipeline) {
                                if ('success' === $pipeline['status']) {
                                    $tagInfo['pipeline'] = $pipeline;
                                }
                            }
                        }
                    }

                    $project = [
                        'id' => $gitlabProjectInfo['id'],
                        'name' => $gitlabProjectInfo['name'],
                        'tags' => $tags,
                        'tag' => $tagInfo
                    ];

                    $projects[] = $project;
                } catch (\InvalidArgumentException $exception) {
                }
            }
            return $projects;
        }
    }

