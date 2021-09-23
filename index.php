<?php

    require_once 'vendor/autoload.php';

    use GitLab\GitLab;
    use GitLab\Command\CreateTagCommand;
    use Gitlab\Command\RemoveTagCommand;
    use GitLab\Command\RemovePipelinesCommand;

    if (file_exists(__DIR__.'/.env') && class_exists(\Symfony\Component\Dotenv\Dotenv::class)) {
        (new \Symfony\Component\Dotenv\Dotenv())->load(__DIR__.'/.env');
    }

    $gitlabUrl = $_ENV['GITLAB_URL'];
    $gitlabToken = $_ENV['GITLAB_TOKEN'];
    $gitlabProjects = $_ENV['GITLAB_PROJECTS'];

    $application = new \Symfony\Component\Console\Application();

    $application->add(new CreateTagCommand());
    $application->add(new RemoveTagCommand());
    $application->add(new RemovePipelinesCommand());

    $gitlab = GitLab::getInstance($gitlabUrl, $gitlabToken, GitLab::formatProjectsStringToArray($gitlabProjects));

    //(new CreateTagCommand())->exec($gitlab, 'orange/releases/2021.4','test-tag-1');
    //(new RemovePipelinesCommand())->exec($gitlab, 1, [1]);
    //(new RemoveTagCommand())->exec($gitlab, 1, 'test-tag-1');