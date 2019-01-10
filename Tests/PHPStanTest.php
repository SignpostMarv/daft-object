<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use Jean85\PrettyVersions;
use OutOfBoundsException;
use PHPStan\Command\AnalyseCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PHPStanTest extends TestCase
{
    public function testPHPStan()
    {
        $version = 'Version unknown';
        try {
            $version = PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion();
        } catch (OutOfBoundsException $e) {
        }

        $application = new Application('PHPStan Checking', $version);
        $application->add(new AnalyseCommand());

        $command = $application->find('analyse');

        static::assertInstanceOf(AnalyseCommand::class, $command);

        $commandTester = new CommandTester($command);

        $commandTester->execute(
            [
                'paths' => [
                    __DIR__ . '/../.php_cs.dist',
                    __DIR__ . '/../src/',
                    __DIR__ . '/../PHPStan/',
                    __DIR__ . '/../Tests/',
                ],
            ],
            [
                'configuration' => __DIR__ . '/../phpstan.neon',
            ]
        );

        $firstLine = trim(current(explode("\n", $commandTester->getDisplay())));

        static::assertSame(
            '0/52 [>---------------------------]   0%',
            $firstLine
        );
    }
}
