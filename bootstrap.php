<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Pornbot;

/**
 * Class Bootstrap
 * @package Pornbot
 */
class Bootstrap
{
    /**
     * Factory to create instance to loader
     * @param array $config
     * @return Bootstrap
     */
    public static function create(array $config)
    {
        if ($config['run'] !== 'cli') {
            throw new \RuntimeException('This script run only CLI');
        }

        return new Bootstrap;
    }

    /**
     * Detect if composer is installed
     * @throws \Exception
     */
    protected function isComposerInstalled()
    {
        if (!file_exists('vendor/autoload.php')) {
            throw new \Exception('Please run "composer init" to execute it.');
        }

        require_once 'vendor/autoload.php';
    }

    /**
     * Retorna uma classe aleatória da pasta de sites
     * @return mixed
     */
    private function sortFromClasses()
    {
        $classes = array_values(array_filter(get_declared_classes(), function($class){
            return strpos($class, 'Pornbot\Sites') !== false && get_parent_class($class) == 'Pornbot\Core\Sitebase';
        }));

        if (empty($classes)) {
            return false;
        }

        return new $classes[mt_rand(0, sizeof($classes) - 1 )];
    }

    /**
     * Start the bot
     */
    public function init()
    {
        try {
            $this->isComposerInstalled();
            if ($className = $this->sortFromClasses()) {
                \Pornbot\Core\Loader::init(['class' => $className]);
            } else {
                print 'Nenhum site encontrado';
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}