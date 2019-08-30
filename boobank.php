<?php
namespace SamKer\BoobankPHP;

require __DIR__.'/vendor/autoload.php';

use SamKer\BoobankPHP\Command\BoobankCheck;
use SamKer\BoobankPHP\Services\Config;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;

$composer = json_decode(file_get_contents('./composer.json'));
$version = $composer->version;

//$config = Yaml::parseFile('./config.yaml');

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('./config.yml');

$containerBuilder->get('boobank.config');
$application = new Application("Boobank PHP", $version);

// ... register commands
$application->add($containerBuilder->get('boobank.command.check'));
$application->add($containerBuilder->get('boobank.command.backendadd'));
$application->add($containerBuilder->get('boobank.command.backendlist'));
$application->add($containerBuilder->get('boobank.command.accountlist'));
$application->add($containerBuilder->get('boobank.command.accounthistory'));
$application->add($containerBuilder->get('boobank.command.mailtest'));

$application->run();