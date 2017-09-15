<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Pornbot\Export;

use Pornbot\Core\Config;
use ActiveRecord\Config as ActiveRecordConfig;
use Pornbot\Models\Video;

/**
 * Class Database
 */
class Database extends ExportBase
{
    /**
     * Diretório dos models
     */
    const MODELS_DIRECTORY = 'Models';

    /**
     * Tipo de conexão do active record
     */
    const CONNECTION_TYPE = 'development';

    /**
     * Configura uma conexão a base de dados
     * @return null
     */
    private function config()
    {
        $config = Config::all();

        $connections = array(
            self::CONNECTION_TYPE => "{$config['db_type']}://{$config['db_user']}:{$config['db_pass']}@{$config['db_host']}/{$config['db_schema']}"
        );

        ActiveRecordConfig::initialize(
            function ($config) use ($connections) {
                $config->set_model_directory(self::MODELS_DIRECTORY);
                $config->set_connections($connections);
                $config->set_default_connection(self::CONNECTION_TYPE);
            });
    }

    /**
     * Processa o registro buscado pelo bot
     * @param $data
     * @return
     */
    public function process($data)
    {
        $this->config();

        $video = new Video($data);
        return $video->save();
    }
}