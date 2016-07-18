<?php

$rootDir = dirname(__DIR__);
define('PUBLIC_CACHE_DIR', $rootDir.'/public/cache');
@mkdir(PUBLIC_CACHE_DIR, 0777, true);

require_once $rootDir.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use JLaso\SimpleStats\Stats;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => $rootDir.'/app/views',
));

$app['twig']->addFunction(
    new Twig_SimpleFunction('statsGraph', function ($graphName, $title, $event, $range, $width, $height) {
        $graph = null;
        switch (strtolower(trim($graphName))){
            case 'scatter':
                $graph = new \JLaso\SimpleStats\Graph\Scatter();
                break;
            case 'bar':
                $graph = new \JLaso\SimpleStats\Graph\Bar();
                break;
            default:
                return "Graph {$graph} not recognized in statsGraph twig function";
        }
        $file = uniqid($graphName.'-').'.svg';

        $graph->draw($title, $event, $range, $width, $height, PUBLIC_CACHE_DIR.'/'.$file);

        return '<img src="cache/'.$file.'" alt="'.$title.'">';

    }, array('pre_escape' => 'html', 'is_safe' => array('html')))
);

$app['twig']->addFunction(
    new Twig_SimpleFunction('statsCount', function ($event, $data) {
        return \JLaso\SimpleStats\Stats::getInstance()->getCountByData($event, $data);
    })
);

$app['twig']->addFunction(
    new Twig_SimpleFunction('userIP', function () {
        return \JLaso\SimpleStats\Stats::getInstance()->getUserIP();
    })
);

$app->get('/', function() use($app) {
    $stats = Stats::getInstance();
    $stats->insert('visits', $stats->getUserIP());
    return $app['twig']->render('index.html.twig');
})->bind('home');

$app->get('/click', function(Request $request) use($app) {
    $stats = Stats::getInstance();

    $data = $request->get('data', 'default');
    $redirect = $request->get('redirect', '/');

    $stats->insert('clicks', $data);

    $userIp = $stats->getUserIP();
    $stats->insert('ips', $userIp);

    return $app->redirect($redirect);
})->bind('click');

$app->run();