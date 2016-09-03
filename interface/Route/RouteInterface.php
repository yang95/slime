<?php
namespace SlimeInterface\Route;

interface RouteInterface
{
    /**
     * @param RouterInterface $Router
     *
     * @return void
     */
    public function addRouter(RouterInterface $Router);

    /**
     * @return bool
     */
    public function run();
}