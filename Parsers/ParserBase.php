<?php
namespace Pornbot\Parsers;

/**
 * Class Parsebase
 * @package Pornbot\Parsers
 */
abstract class Parserbase
{
    /**
     * @var \Pornbot\Core\Sitebase
     */
    protected $instance = null;

    /**
     * Inicia o parsing do método escolhido
     * @return mixed
     */
    abstract function start();

    /**
     * Altera a instância do site escolhido
     * @param \Pornbot\Core\Sitebase $instance
     */
    public function setInstance(\Pornbot\Core\Sitebase $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Retorna a instância do site escolhido
     * @return \Pornbot\Core\Sitebase
     */
    public function getInstance()
    {
        return $this->instance;
    }
}