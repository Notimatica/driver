<?php namespace Notimatica\Driver\Contracts;

class FilesStorage
{
    /**
     * @var string
     */
    protected $root = '';

    /*
     * Create a new FilesStorage.
     *
     * @param string $root
     */
    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * Return root path.
     *
     * @param  string $path
     * @return string
     */
     public function getRoot($path = '')
     {
         return $this->root . $path;
     }
}