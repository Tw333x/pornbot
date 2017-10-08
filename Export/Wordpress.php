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
     *
     * @param $data
     *
     * @throws PornbotException
     * @return void
     */
    public function process($data)
    {
        $wp_dir  = Config::get('wp_dir');
        $wp_load = $wp_dir . '/wp-load.php';

        if ( ! file_exists($wp_load)) {
            throw new PornbotException('Erro: arquivo wp-load.php não encontrado, reveja o diretório no config.php');
        }

        require_once $wp_load;
        require_once $wp_dir . '/wp-admin/includes/taxonomy.php';

        if ( ! function_exists('add_post_meta')) {
            throw new PornbotException('Erro: função add_post_meta não encontrada');
        }

        $category_ids = [];
        $categories   = explode(',', $data['category']);
        foreach ($categories as $category) {
            if ($category_id = \wp_create_category($category)) {
                $category_ids[] = $category_id;
            }
        }

        remove_filter('content_save_pre', 'wp_filter_post_kses');
        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');

        if ($post_id = $this->insert_post($data, $category_ids)) {

            add_filter('content_save_pre', 'wp_filter_post_kses');
            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

            $this->insert_thumbnail($post_id, $data);

            $customfields = array(
                'duracao' => $data['duration'],
                'views'   => 0
            );

            foreach ($customfields as $meta_key => $meta_value) {
                add_post_meta($post_id, $meta_key, $meta_value);
            }

            Functions::printlog('Inseriu o video: ' . $data['title']);
        }
    }

    /**
     * Método usado para inserir o thumbnail na postagem
     *
     * @param $post_id
     * @param $data
     */
    private function insert_thumbnail($post_id, $data)
    {
        $wp_dir      = Config::get('wp_dir');
        $file        = $data['thumbnail'];
        $filename    = basename($file);
        $upload_file = \wp_upload_bits($filename, null, @file_get_contents($file));
        if ( ! $upload_file['error']) {

            $wp_filetype = \wp_check_filetype($filename, null);
            $attachment  = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_parent'    => $post_id,
                'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attachment_id = \wp_insert_attachment($attachment, $upload_file['file'], $post_id);
            if ( ! \is_wp_error($attachment_id)) {
                require_once($wp_dir . '/wp-admin/includes/image.php');
                $attachment_data = \wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
                \wp_update_attachment_metadata($attachment_id, $attachment_data);
                \set_post_thumbnail($post_id, $attachment_id);
            }
        }
    }

    /**
     * Método usado para inserir uma postagem no wordpress
     * @param $data
     * @param $category_ids
     * @return mixed
     * @throws PornbotException
     */
    private function insert_post($data, array $category_ids)
    {
        if ( ! function_exists('wp_insert_post') || ! function_exists('sanitize_title')) {
            throw new PornbotException('Erro: funções wp_insert_post e sanitize_title não encontradas');
        }

        $slug       = sanitize_title($data['title']);
        $attributes = array(
            'post_author'    => 1,
            'post_content'   => $data['embed'],
            'post_title'     => $data['title'],
            'post_category'  => $category_ids,
            'post_status'    => 'publish',
            'comment_status' => 'open',
            'post_name'      => $slug,
            'post_parent'    => 0,
            'guid'           => site_url() . '/' . $slug,
            'post_type'      => 'post',
            'filter'         => true
        );

        return wp_insert_post($attributes);
    }
}