<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;

class BaseRequest extends HttpRequest
{
    // 根据实际情况调整长度
    const JSON_MAX_LENGTH = 65535;

    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $content = $this->getContent();

            $parameters = Str::length($content) > static::JSON_MAX_LENGTH ? [] : (array) json_decode($content, true);

            $this->json = new ParameterBag($parameters);
        }

        if (is_null($key)) {
            return $this->json;
        }

        return data_get($this->json->all(), $key, $default);
    }
}
