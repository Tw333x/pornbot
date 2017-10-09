<?php
namespace Pornbot\Core;

abstract class Sitebase
{
    /**
     * Nome do site
     * @return string
     */
    abstract public function name();

    /**
     * Renderizador utilizado
     * @return string
     */
    abstract public function type();

    /**
     * Url do site a ser buscado
     * @return string
     */
    abstract public function url();

    /**
     * Retorna uma página de vídeos aleatória
     * @param $page
     * @return mixed
     */
    abstract public function random($page);

    /**
     * Método usado para buscar o título do vídeo
     * @return array
     */
    abstract public function title();

    /**
     * Método usado para buscar o thumbnail do vídeo
     * @return mixed
     */
    abstract public function thumbnail();

    /**
     * Método usado para buscar os links dos vídeos
     * @return mixed
     */
    abstract public function link();

    /**
     * Método usado para buscar as categorias do vídeo
     * @return mixed
     */
    abstract public function category();

    /**
     * Método usado para buscar o código de incorporação do vídeo
     * @return mixed
     */
    abstract public function embed();

    /**
     * Converte todos os arrays retornados nos sites para objeto
     * @param $name
     * @return mixed
     * @throws PornbotException
     */
    public function __get($name)
    {
        if (!method_exists($this, $name)) {
            throw new PornbotException("Método {$name} não encontrado.");
        }

        $call = $this->{$name}();

        if (is_array($call)) {
            return (object)$call;
        }

        return $call;
    }
}