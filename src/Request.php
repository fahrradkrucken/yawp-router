<?php

namespace FahrradKruken\YAWP\Router;

class Request
{
    private $params = [];
    private $headers = [];
    private $currentRoute = null;

    /**
     * Request constructor.
     *
     * @param Route $currentRoute
     */
    public function __construct($currentRoute)
    {
        $this->currentRoute = $currentRoute;

        $input = [];
        if (wp_is_json_request()) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE)
                $input = [];
        } else {
            $input = $_REQUEST;
        }

        if (!empty($input)) {
            foreach ($input as $paramName => $paramValue) {
                if (is_string($paramValue))
                    $this->params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_STRING);
                elseif (is_int($paramValue))
                    $this->params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_NUMBER_INT);
                elseif (is_float($paramValue))
                    $this->params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_NUMBER_FLOAT);
                elseif (is_numeric($paramValue))
                    $this->params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_NUMBER_INT);
                elseif (is_array($paramValue))
                    $this->params[$paramName] = filter_var_array($paramValue);
                else
                    $this->params[$paramName] = filter_var($paramValue, FILTER_DEFAULT, ['options' => ['default' => '']]);
            }
        }

        $this->headers = getallheaders();
    }

    /**
     * Get filtered parameter (variable) from $_REQUEST or http request body (php://input).
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }

    /**
     * Add / Update Request parameter - useful for validation or some other features that you can do BEFORE your action.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    public function setParam($name, $value)
    {
        return $this->params[$name] = $value;
    }

    /**
     * Get All request parameters as array
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Update All request parameters with the new parameters array,
     *
     * @param array $params
     *
     * @return mixed
     */
    public function setParams($params)
    {
        return $this->params = $params;
    }

    /**
     * Get current request HTTP Header
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    /**
     * Get Route that executing right now.
     *
     * @return Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
}