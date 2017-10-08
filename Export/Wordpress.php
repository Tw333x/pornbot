<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Pornbot\Export;

use Pornbot\Core\Functions;
use Pornbot\Core\Config;
use Pornbot\Core\PornbotException;

/**
 * Class Wordpress
 * @package Pornbot\Export
 */
class Wordpress extends ExportBase
{
    /**
     * Método usado para processar todo conteúdo que o bot recebeu
     * @param $data
     * @throws PornbotException
     * @return void
     */
    public function process($data)
    {
        $wp_dir = Config::get('wp_dir');
        $wp_load = $wp_dir . '/wp-load.php';

        if (!file_exists($wp_load)) {
            throw new PornbotException('Erro: arquivo wp-load.php não encontrado, reveja o diretório no config.php');
        }

        if (!function_exists('add_post_meta')) {
            throw new PornbotException('Erro: função add_post_meta não encontrada');
        }

        require_once $wp_load;

        if ($post_id = $this->insert_post($data)) {
            $customfields = array(
                'duracao' => $data['duration'],
                'views' => 0
            );

            foreach ($customfields as $meta_key => $meta_value) {
                add_post_meta($post_id, $meta_key, $meta_value);
            }

            Functions::printlog('Inseriu o video: ' . $data['title']);
        }
    }

    /**
     * Método usado para inserir uma postagem no wordpress
     * @param $data
     * @return mixed
     * @throws PornbotException
     */
    private function insert_post($data)
    {
        if (!function_exists('wp_insert_post') || !function_exists('sanitize_title')) {
            throw new PornbotException('Erro: funções wp_insert_post e sanitize_title não encontradas');
        }

        $slug = sanitize_title($data['title']);
        $attributes = array(
            'post_author' => 1,
            'post_content' => '',
            'post_title' => $data['title'],
            'post_status' => 'publish',
            'comment_status' => 'open',
            'post_name' => $slug,
            'post_parent' => 0,
            'guid' => site_url() . "/videos/{$slug}",
            'post_type' => 'videos',
        );

        return wp_insert_post($attributes);
    }
}