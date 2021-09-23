<?php

    namespace GitLab;

    class GitLab
    {
        private string $url;

        private string $token;

        private array $projects = [];

        /**
         * GitLab constructor.
         * @param string $url
         * @param string $token
         * @param array $projects
         */
        public function __construct(string $url, string $token, array $projects)
        {
            $this->url = $url;
            $this->token = $token;
            $this->projects = $projects;
        }

        /**
         * @return array
         */
        public function getProjects(): array
        {
            return $this->projects;
        }

        public static function getInstance(string $url, string $token, array $projects): GitLab
        {
            $gitlab = new GitLab($url, $token, $projects);

            foreach ($gitlab->getProjects() as $id => $name) {

                if (false === $gitlab->isAvailableProject($id)) {
                    throw new \Exception('['.$name.'] is invalid');
                }
            }

            return $gitlab;
        }

        private function isAvailableProject(string $id): bool
        {
            try {
                $this->call('GET', '/api/v4/projects/' . $id);
                return true;
            } catch (\InvalidArgumentException $exception) {
                return false;
            }
        }

        public static function formatProjectsStringToArray(string $projects): array
        {
            $data = [];
            foreach (explode(',', $projects) as $project) {
                list($name, $id) = explode(':', $project);
                $data[$id] = $name;
            }
            return $data;
        }

        /**
         * @param string $method
         * @param string $url
         * @param array|null $data
         * @return array|null
         * @throws \Exception
         */
        public function call(string $method, string $url, ?array $data = null): ?array
        {
            if (is_array($data)) {
                $url = $url .'?'.http_build_query($data);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url.$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'PRIVATE-TOKEN: ' . $this->token,
            ]);

            /*
            if ('POST' === strtoupper($method)) {
                $payload = json_encode($data);

                // Use POST request
                curl_setopt($ch, CURLOPT_POST, true);

                // Set payload for POST request
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                // Set HTTP Header for POST request
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]);
            }
            */

            $server_output = curl_exec($ch);
            $info = curl_getinfo($ch);
            if (isset($info['http_code']) && $info['http_code'] >= 400 && $info['http_code'] < 499) {
                throw new \InvalidArgumentException('Client error: ['.$url.'] ['.$server_output.']', $info['http_code']);
            } else if (isset($info['http_code']) && $info['http_code'] >= 500 && $info['http_code'] < 599) {
                throw new \Exception('Server error', $info['http_code']);
            }
            curl_close($ch);
            return json_decode($server_output, true);
        }
    }