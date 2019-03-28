<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 023 23.03.2019
 * Time: 18:22
 */

namespace FahrradKruken\yawpRouter;


class Response
{
    const STATUS_OK = 200;
    const STATUS_NO_CONTENT = 204;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_NOT_ACCEPTABLE = 406;
    const STATUS_CONFLICT = 409;
    const STATUS_GONE = 410;

    const STATUS_INTERNAL_SERVER_ERROR = 500;

    private $statusCode = null;

    public $data;

    public function getStatus($code = self::STATUS_OK)
    {
        $this->statusCode = intval($code);
    }

    public function renderView($viewAction, $viewVariables = [])
    {
        ob_start();
        if (is_callable($viewAction)) {
            call_user_func_array($viewAction, $viewVariables);
        } elseif (is_file($viewAction)) {
            if (!empty($viewVariables)) extract($viewVariables, EXTR_OVERWRITE);
            include($viewAction);
        }
        $this->data = ob_get_clean();
    }

    public function send()
    {
        if (wp_doing_ajax() || wp_is_json_request()) {
            if (is_wp_error($this->data)) {
                if (empty($this->statusCode)) $this->statusCode = self::STATUS_BAD_REQUEST;
                wp_send_json_error($this->data, $this->statusCode);
            } elseif (empty($this->data)) {
                wp_send_json_error(new \WP_Error(self::STATUS_NO_CONTENT, 'NO CONTENT'), self::STATUS_NO_CONTENT);
            } else {
                if (empty($this->statusCode)) $this->statusCode = self::STATUS_OK;
                wp_send_json_success($this->data, $this->statusCode);
            }
        } else {
            if (is_wp_error($this->data)) {
                if (empty($this->statusCode)) $this->statusCode = self::STATUS_BAD_REQUEST;
                wp_die($this->data, null, ['response' => $this->statusCode]);
            } elseif (empty($this->data)) {
                wp_die(new \WP_Error(self::STATUS_NO_CONTENT, 'NO CONTENT'), null, ['response' => self::STATUS_NO_CONTENT]);
            } else {
                if (empty($this->statusCode)) $this->statusCode = self::STATUS_OK;
                status_header($this->statusCode);
                echo $this->data;
            }
        }
        die();
    }
}