<?php

require_once __DIR__.'/../vendor/autoload.php';

use \GitLab\GitLab;
use \GitLab\Command\RetrieveCommitCommand;
use \GitLab\Command\RetrieveTagCommand;
use Symfony\Component\Dotenv\Dotenv;
use \Symfony\Component\HttpFoundation\Request;

if (file_exists(__DIR__.'/../.env') && class_exists(Dotenv::class)) {
    (new Dotenv())->load(__DIR__.'/../.env');
}

$gitlabUrl = $_ENV['GITLAB_URL'];
$gitlabToken = $_ENV['GITLAB_TOKEN'];
$gitlabProjects = $_ENV['GITLAB_PROJECTS'];
$appName = $_ENV['APP_NAME'];

$gitlab = GitLab::getInstance($gitlabUrl, $gitlabToken, GitLab::formatProjectsStringToArray($gitlabProjects));

$app = new Silex\Application([
    'debug' => true,
]);

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
));

$app['twig']->addGlobal('appName', $appName);

$app->get('/branch', function(Request $request) use($app, $gitlab) {

    $branches = (new RetrieveCommitCommand())->getBranches($gitlab);
    $repositories = (new RetrieveCommitCommand())->getHashes($gitlab, $request->get('branch', RetrieveCommitCommand::DEFAULT_BRANCH));

    return $app['twig']->render('index/branch.html.twig', [
        'repositories' => $repositories,
        'menu_branches' => $branches,
    ]);
});

$app->get('/tag', function(Request $request) use($app, $gitlab) {

    $listTags = [];
    $projects = (new RetrieveTagCommand())->getTags($gitlab, $request->get('tag'));
    foreach ($projects as $project) {
        foreach ($project['tags'] as $tag) {
            if (false === in_array($tag, $listTags)) {
                $listTags[] = $tag;
            }
        }
    }

    return $app['twig']->render('index/tag.html.twig', [
        'projects' => $projects,
        'menu_tags' => $listTags,
    ]);
});

$app->run();
