<?php
namespace Icecave\Testing\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    protected function readGitConfig($key)
    {
        $exitCode = 0;
        $result = exec('git config --global ' . escapeshellarg($key), $output, $exitCode);
        $result = trim($result);

        if (0 === $exitCode && '' !== $result) {
            return $result;
        }

        return null;
    }

    protected function travisKey($packageName) {
        return file_get_contents('https://api.travis-ci.org/repos/' . $packageName . '/key');
    }

    protected function encryptToken($key, $token) {
        return '<todo:encrypt-key>';
    }

    protected function cloneSkeletons($projectRoot, array $skeletons, array $variables)
    {
        $command = $projectRoot . '/vendor/bin/ict-chassis clone ';

        foreach ($skeletons as $skel) {
            $command .= ' ' . escapeshellarg($projectRoot . '/vendor/icecave/testing/res/skel.' . $skel);
        }

        foreach ($variables as $key => $value) {
            $command .= ' --define ' . escapeshellarg($key . '=' . $value);
        }

        $command .= ' --output-path ' . escapeshellarg($projectRoot);

        $this->passthru($command);
    }

    protected function passthru($command)
    {
        $arguments = array_slice(func_get_args(), 1);
        $arguments = array_map('escapeshellarg', $arguments);
        $command   = vsprintf($command, $arguments);

        $exitCode = null;
        passthru($command, $exitCode);

        if (0 !== $exitCode) {
            throw new RuntimeException('Failed executing "' . $command . '".');
        }
    }
}