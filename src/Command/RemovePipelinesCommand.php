<?php

    namespace GitLab\Command;

    use Gitlab\GitLab;
    use Symfony\Component\Console\Command\Command;

    class RemovePipelinesCommand extends Command
    {
        public function getName(): string
        {
            return __CLASS__;
        }

        private function getPipelines(GitLab $gitLab, string $idProject): array
        {
            $data = $gitLab->call('GET', 'https://gitlab.rd.alphanetworks.tv/api/v4/projects/'.$idProject.'/pipelines?status=canceled');
            $tags = [];
            foreach ($data as $d) {
                if (isset($d['id']) && in_array($d['status'], ['running', 'canceled', 'failed', 'created'])) {
                    $tags[$idProject] = $d['id'];
                }
            }
            return $tags;
        }

        /**
         * @param GitLab $gitLab
         * @param string $idProject
         * @param string $idPipeline
         * @return array|null
         * @throws \Exception
         */
        private function deletePipeline(GitLab $gitLab, string $idProject, string $idPipeline): ?array
        {
            return $gitLab->call('DELETE', "https://gitlab.rd.alphanetworks.tv/api/v4/projects/'.$idProject.'/pipelines/" . $idPipeline);
        }

        public function exec(GitLab $gitLab, string $idProject, array $idsPipeline = [])
        {
            $stdin = fopen('php://stdin', 'r');

            $token = getenv('PRIVATE_TOKEN');
            if (!$token) {
                //Token
                echo "Token:";
                $token = trim(fgets($stdin));
                putenv("PRIVATE_TOKEN=" . $token);
            }

            echo "#START\n";

            do {
                foreach ($idsPipeline as $idPipeline) {
                    var_export([$idPipeline => $this->deletePipeline($gitLab, $idProject, $idPipeline)]);
                    echo $idPipeline . "\n";
                }
                $ids = $this->getPipelines($gitLab, $idProject);
            } while (is_array($ids) && count($ids) > 0);

            echo "\n#END";
        }
    }
