<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 023 23.03.2019
 * Time: 18:22
 */

namespace FahrradKruken\yawpRouter;

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

    public function getParam($name)
    {
        return $this->params[$name];
    }

    public function setParam($name, $value)
    {
        return $this->params[$name] = $value;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        return $this->params = $params;
    }

    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    /**
     * @return Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
}