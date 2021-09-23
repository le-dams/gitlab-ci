<?php

    namespace Gitlab\Command;

    use Gitlab\GitLab;
    use Symfony\Component\Console\Command\Command;

    class RemoveTagCommand extends Command
    {
        public function getTags(GitLab $gitLab, string $idProject, string $search)
        {
            $data = $gitLab->call('GET', 'https://gitlab.rd.alphanetworks.tv/api/v4/projects/'.$idProject.'/repository/tags?search=' . $search);
            $tags = [];
            foreach ($data as $d) {
                if (isset($d['name'])) {
                    $tags[] = $d['name'];
                }
            }
            return $tags;
        }

        public function removeTag(GitLab $gitLab, string $idProject, string $tag)
        {
            return $gitLab->call('DELETE', "https://gitlab.rd.alphanetworks.tv/api/v4/projects/'.$idProject.'/repository/tags/" . urlencode($tag));
        }

        public function exec(GitLab $gitLab, string $idProject, string $search)
        {
            # Search
            if (strlen($search) < 3) {
                echo "\nBad value for search [{$search}]";
                exit(1);
            }
            echo "#START with [{$search}]\n";

            $tags = [];
            do {
                foreach ($tags as $tag) {
                    $this->removeTag($gitLab, $idProject, $tag);
                    echo $tag . "\n";
                }
                $tags = $this->getTags($gitLab, $idProject, $search);
            } while (is_array($tags) && count($tags) > 0);

            echo "\n#END";
        }
    }