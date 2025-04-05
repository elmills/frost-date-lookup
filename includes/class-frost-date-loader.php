<?php

class Frost_Date_Loader {

    protected $actions = [];
    protected $filters = [];

    public function add_action( $hook, $component, $callback ) {
        $this->actions[] = [
            'hook'      => $hook,
            'component' => $component,
            'callback'  => $callback,
        ];
    }

    public function add_filter( $hook, $component, $callback ) {
        $this->filters[] = [
            'hook'      => $hook,
            'component' => $component,
            'callback'  => $callback,
        ];
    }

    public function run() {
        foreach ( $this->actions as $action ) {
            add_action( $action['hook'], [ $action['component'], $action['callback'] ] );
        }

        foreach ( $this->filters as $filter ) {
            add_filter( $filter['hook'], [ $filter['component'], $filter['callback'] ] );
        }
    }
}