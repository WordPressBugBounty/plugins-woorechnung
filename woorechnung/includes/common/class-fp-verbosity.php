<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Verbosity' ) ):

/**
 * Faktur Pro Verbosity Class
 *
 * @class    FP_Verbosity
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Verbosity
{
    /**
     * Get verbosity string of method or function.
     *
     * @param  string $message
     * @param  mixed $class
     * @param  string $function
     * @param  mixed $data
     * @return string
     */
    public function get_verbosity_string( $message, $class, $function, $data )
    {
        if ( !empty( $function ) ) {
            if ( !empty( $message ) ) {
                $message .= ' ';
            }
            if ( !empty( $class ) ) {
                $message .= $this->get_method_string( $class, $function );
            } else {
                $message .= $this->get_function_string( $function );
            }
        }

        if ( !empty( $data ) ) {
            if ( !empty( $message ) ) {
                $message .= ' - ';
            }
            $message .= 'data: ' . $this->get_data_string( $data );
        }

        return $message;
    }

    /**
     * Get string of data.
     *
     * @param  mixed $data
     * @return string
     */
    public function get_data_string( $data )
    {
        return print_r( $data, true );
    }

    /**
     * Get string of a method.
     *
     * @param  mixed $class
     * @param  string $method
     * @return string
     */
    public function get_method_string( $class, $method )
    {
        $meth_str = '';
        $reflection_method = new ReflectionMethod( $class, $method );
        $parameters = $this->get_method_parameters( $reflection_method );
        if ( is_string( $class ) ) {
            $meth_str .= $class . '::';
        }
        $meth_str .= $this->get_method_name( $reflection_method );
        $meth_str .= '(' . implode( ', ', $parameters ) . ')';
        return $meth_str;
    }

    /**
     * Get name of a method.
     *
     * @param  ReflectionMethod $reflection_method
     * @return string
     */
    public function get_method_name( ReflectionMethod $reflection_method )
    {
        return $reflection_method->getName();
    }

    /**
     * Get parameters of a method.
     *
     * @param  ReflectionMethod $reflection_method
     * @return array<string>
     */
    public function get_method_parameters( ReflectionMethod $reflection_method )
    {
        $parameters = array();
        foreach ( $reflection_method->getParameters() as $param ) {
            $parameters[] = '$' . $param->name;
        }
        return $parameters;
    }

    /**
     * Get string of a function.
     *
     * @param  string $function
     * @return string
     */
    public function get_function_string( $function )
    {
        $fnc_str = '';
        $reflection_function = new ReflectionFunction( $function );
        $parameters = $this->get_function_parameters( $reflection_function );
        $fnc_str .= $this->get_function_name( $reflection_function );
        $fnc_str .= '(' . implode( ', ', $parameters ) . ')';
        return $fnc_str;
    }

    /**
     * Get name of a function.
     *
     * @param  ReflectionFunction $reflection_function
     * @return string
     */
    public function get_function_name( ReflectionFunction $reflection_function )
    {
        return $reflection_function->getName();
    }

    /**
     * Get parameters of a function.
     *
     * @param  ReflectionFunction $reflection_function
     * @return array<string>
     */
    public function get_function_parameters( ReflectionFunction $reflection_function )
    {
        $parameters = array();
        foreach ( $reflection_function->getParameters() as $param ) {
            $parameters[] = '$' . $param->name;
        }
        return $parameters;
    }
}

endif;
